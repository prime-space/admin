<?php namespace App\Controller;

use App\Annotation\AccessRule;
use App\Authenticator;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientRequestException;
use App\Repository\TicketMessageRepository;
use App\Repository\TicketRepository;
use App\ServiceClient;
use App\TelegramSender;
use App\VueViewCompiler;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Ewll\DBBundle\DB\Client as DbClient;

class TicketController extends Controller implements SignControllerInterface
{
    private $repositoryProvider;
    private $serviceClient;
    private $vueViewCompiler;
    private $defaultDbClient;
    private $authenticator;
    private $telegramSender;
    private $domain;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        ServiceClient $serviceClient,
        VueViewCompiler $vueViewCompiler,
        DbClient $defaultDbClient,
        Authenticator $authenticator,
        TelegramSender $telegramSender,
        string $domain
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->serviceClient = $serviceClient;
        $this->vueViewCompiler = $vueViewCompiler;
        $this->defaultDbClient = $defaultDbClient;
        $this->authenticator = $authenticator;
        $this->telegramSender = $telegramSender;
        $this->domain = $domain;
    }

    /** @AccessRule(name="ticketsRule") */
    public function ticket(int $ticketId)
    {
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        /** @var Ticket $ticket */
        $ticket = $ticketRepository->findOneBy(['id' => $ticketId]);
        if ($ticket === null) {
            return new JsonResponse([], 404);
        }
        $ticket->isReplied = false;
        $ticketRepository->update($ticket);

        /** @var TicketMessageRepository $ticketMessagesRepository */
        $ticketMessagesRepository = $this->repositoryProvider->get(TicketMessage::class);
        $messages = $ticketMessagesRepository->getMessagesByTicketIdOrderedById($ticketId);
        $responsibleUsersIndexedById = $this->repositoryProvider->get(User::class)->findByRelativeIndexed($messages);
        $data = [];
        /** @var TicketMessage $message */
        foreach ($messages as $message) {
            $responsibleUser = $responsibleUsersIndexedById[$message->userId] ?? null;
            $data['messages'][] = $message->compileVueView($ticket, $responsibleUser);
        }
        $data['responsibleUserId'] = $ticket->responsibleUserId;
        $data['serviceId'] = $ticket->serviceId;
        $data['ticketSubject'] = $ticket->subject;

        return new JsonResponse($data);
    }

    /** @AccessRule(name="ticketsRule") */
    public function tickets(int $limit, int $pageId)
    {
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        $tickets = $ticketRepository->getTicketsOrderedByUnreadAndDateWithPagination($limit, $pageId);
        $total = $ticketRepository->getFoundRows();
        $services = $this->repositoryProvider->get(Service::class)->findByRelativeIndexed($tickets);
        $users = $this->repositoryProvider->get(User::class)->findByRelativeIndexed($tickets, 'responsibleUserId');
        $views = [];
        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            /** @var Service $service */
            $service = $services[$ticket->serviceId];
            if ($ticket->responsibleUserId !== null) {
                /** @var User $responsibleUser */
                $responsibleUser = $users[$ticket->responsibleUserId];
                $fullUserName = $responsibleUser->compileFullName();
            } else {
                $fullUserName = '';
            }
            $views[] = $ticket->compileView($service->domain, $fullUserName);
        }

        return new JsonResponse(['tickets' => $views, 'total' => $total]);
    }

    /** @AccessRule(name="ticketsRule") */
    public function message(Request $request)
    {
        $formBuilder = $this->createFormBuilder()
            ->add('message', TextType::class, ['constraints' => [
                new NotBlank(['message' => 'fill field']),
            ]])
            ->add('ticketId', IntegerType::class, ['constraints' => [
                new NotBlank(['message' => 'fill field']),
            ]]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return new JsonResponse([], 400);
        }
        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $data = $form->getData();
            /** @var Ticket $ticket */
            $ticket = $this->repositoryProvider->get(Ticket::class)->findOneBy([
                'id' => $data['ticketId'],
            ]);
            if ($ticket === null) {
                return new JsonResponse([], 404);
            }
            $this->defaultDbClient->beginTransaction();
            try {
                $userId = $this->authenticator->getUser()->id;
                $ticketMessage = TicketMessage::create($ticket->id, $data['message'], $userId);
                $this->repositoryProvider->get(TicketMessage::class)->create($ticketMessage);
                $this->serviceClient->sendMessage($ticket->serviceId, $ticket->serviceTicketId);
                $this->defaultDbClient->commit();
            } catch (ServiceClientRequestException $e) {
                $this->defaultDbClient->rollback();
                $form->get('message')->addError(new FormError('Cannot send message'));
                throw new FormValidationException();
            }

            return new JsonResponse([]);
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));

            return new JsonResponse(['errors' => $errors], 400);
        }
    }

    /** @AccessRule(name="ticketsRule") */
    public function responsible(Request $request)
    {
        $formBuilder = $this->createFormBuilder()
            ->add('responsibleUserId', IntegerType::class, ['constraints' => [
                new NotBlank(['message' => 'fill field']),
            ]])
            ->add('ticketId', IntegerType::class, ['constraints' => [
                new NotBlank(['message' => 'fill field']),
            ]]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return new JsonResponse([], 400);
        }
        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $data = $form->getData();
            /** @var Ticket $ticket */
            $ticket = $this->repositoryProvider->get(Ticket::class)->findOneBy([
                'id' => $data['ticketId'],
            ]);
            if ($ticket === null) {
                return new JsonResponse([], 404);
            }
            if ($ticket->responsibleUserId === $data['responsibleUserId']) {
                return new JsonResponse([], 400);
            }
            /** @var User $user */
            $user = $this->repositoryProvider->get(User::class)->findOneBy([
                'id' => $data['responsibleUserId'],
            ]);
            if ($user === null) {
                return new JsonResponse([], 404);
            }
            $ticket->responsibleUserId = $user->id;
            $this->repositoryProvider->get(Ticket::class)->update($ticket);
            $url = "https://{$this->domain}/#/tickets/{$ticket->id}";
            $message = "Ticket {$url} has been assigned to you";
            $recipientTelegramId = $user->telegramId;
            if ($recipientTelegramId !== null) {
                $this->telegramSender->send($message, $recipientTelegramId);
            }

            return new JsonResponse([]);
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));

            return new JsonResponse(['errors' => $errors], 400);
        }
    }
}
