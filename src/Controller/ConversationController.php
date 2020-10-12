<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use App\Entity\Conversation;
use App\Entity\Participant;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/conversations", name="conversations_")
 */
class ConversationController extends AbstractController
{
    
    private $userRepository;
    
    private $entityManager;
    
    private $conversationRepository;
    
    public function __construct(UserRepository $userRepository,
                                EntityManagerInterface $entityManager,
                                ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }
    
    /**
     * @Route("/", name="newConversations", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($otherUser);
        
        if(is_null($otherUser)) {
            throw new \Exception("The user was not found");
        }
        
        // cannot create a conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("Cannot create conversation with myself");
        }
        
        // check if conversation already exists
        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );
        
        if (count($conversation)) {
            throw new \Exception("Conversation already exists");
        }
        
        // Conversation does not exist, so create new one
        $conversation = new Conversation();
        
        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);
        
        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);
        
        $this->entityManager->getConnection()->beginTransaction();
        
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);
            
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $ex) {
            $this->entityManager->rollback();
            throw $ex;
        }
        
        
        return $this->json([
            'id' => $conversation->getId()
        ], Response::HTTP_CREATED, [], []);
    }
    
    /**
     * @Route("/", name="getConversations", methods={"GET"})
     * @return JsonResponse
     */
    public function getConversations() {
        $conversations = $this->conversationRepository
                ->findConversationsByUser($this->getUser()->getId());
        
        return $this->json($conversations);
    }
}
