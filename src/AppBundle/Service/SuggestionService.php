<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\Suggestion;
use Doctrine\ORM\EntityManagerInterface;

class SuggestionService {

    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $currentId
     * @param $data
     * @param $csrfToken
     * @param $friends
     * @return array
     */
    public function validateSuggestion($currentId, $data, $csrfToken, $friends)
    {
        $requestedToken = $data['csrf_token'];
        $targetUser     = (int)$data['target_user'];

        if ( $csrfToken == $requestedToken ) {
            $count = $this->doctrine
                ->getRepository(\AppBundle\Entity\Suggestion::class)
                ->getCountSuggestions($currentId);

            if ( (int)$count[1] >= 50 ) {
                $responce = ['status' => 'error', 'description' => 'You can`t send more requests!!!'];

                return $responce;
            }

            if ( in_array($targetUser, $friends) ) {
                $responce = ['status' => 'error', 'description' => 'You are already Friends!!!'];

                return $responce;
            }

            $checkForExistSuggestion = $this->doctrine
                                        ->getRepository(Suggestion::class)
                                        ->checkIfExistSuggestion($currentId, $targetUser);

            if ( $checkForExistSuggestion != null ) {
                $responce = ['status' => 'error', 'description' => 'This Suggestion already exists!!!'];

                return $responce;
            }

            $suggestion = new Suggestion();

            $suggestion->setAcceptUser($this->doctrine
                                            ->getRepository(User::class)
                                            ->find($targetUser));

            $suggestion->setSuggestUser($this->doctrine
                                            ->getRepository(User::class)
                                            ->find($currentId));

            $em = $this->doctrine;
            $em->persist($suggestion);
            $em->flush();

            $responce = ['status' => 'success'];

            return $responce;
        }else {
            $responce = ['status' => 'error', 'description' => 'Wrong CSRF Token'];

            return $responce;
        }
    }

    public function acceptSuggestion($currentId, $data, $csrfToken, $friends)
    {
        $requestedToken = $data['csrf_token'];
        $suggestionId   = $data['suggestionId'];

        if ( $requestedToken == $csrfToken ) {

            $suggestion = $this->doctrine
                                ->getRepository(Suggestion::class)
                                ->find($suggestionId);

            if ( $suggestion == null ) {
                $responce = ['status' => 'error', 'description' => 'This suggestion doesn`t exist!!!'];

                return $responce;
            }

            if ( $suggestion->isDisabled() == 1 ) {
                $responce = ['status' => 'error', 'description' => 'This is not valid suggestion!!!'];

                return $responce;
            }

            $acceptUser  = $suggestion->getAcceptUser()->getId();
            $suggestUser = $suggestion->getSuggestUser()->getId();

            if ( $acceptUser != $currentId ) {
                $responce = ['status' => 'error', 'description' => 'This is not valid suggestion!!!'];

                return $responce;
            }

            if ( in_array($suggestUser, $friends) ) {
                $responce = ['status' => 'error', 'description' => 'This is not valid suggestion!!!'];

                return $responce;
            }

            $currentUser = $this->doctrine
                                ->getRepository(User::class)
                                ->find($currentId);

            $currentUser->addFriend($suggestion->getSuggestUser());

            $em = $this->doctrine;
            $em->persist($currentUser);
            $em->flush();

            $this->doctrine
                ->getRepository(Suggestion::class)
                ->disableSuggestion($suggestionId);

            $responce = ['status' => 'success', 'newFriend' => $suggestUser];

            return $responce;

        }else {
            $responce = ['status' => 'error', 'description' => 'CSRFToken is not valid!!!'];

            return $responce;
        }

    }
}