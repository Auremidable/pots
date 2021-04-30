<?php
namespace App\Modules;

use Exception;

class surveyModule extends AbstractModule {
    public $name = 'survey';
    public $data = "";
    public $title = 'Sondage';
    public $picture = 'https://img.icons8.com/ios-filled/50/75E2FF/ask-question.png';

    public function getMainPage($error = null) {
        $current_data = json_decode($this->eventModule->getData(), true);
        $additionnal_data = ["surveys" => $current_data];
        if (!empty($error))
            $additionnal_data['error'] = $error;
        return $this->render("mainpage", $additionnal_data);
    }

    public function setMainPage() {
        return $this->getMainPage();
    }

    public function setButtonPressed() {
        return $this->render('newquestion');
    }

    public function setNewQuestion() {
        try {
            $form_data = json_decode($this->request->getContent(), true);
            $current_data = json_decode($this->eventModule->getData(), true);
            if(is_null($current_data))
                $current_data = array();

            if (!isset($form_data['question']) || empty($form_data['question']))
                throw new Exception('Veuillez entrer une question');
            
            $newquestion = array(
                'question' => $form_data['question'],
                'multiple' => $form_data['multiple'] === "y",
                'editor'   => $this->user->getId()
            );
    
            $suggestions = array();
            $position = 0;
            for ($i = 1; $i < 6; $i++) {
                $suggestion = array(
                    'participants' => array()
                );
                if (isset($form_data['suggestion_' . $i]) && !empty($form_data['suggestion_' . $i])) {
                    $suggestion['content'] = $form_data['suggestion_' . $i];
                    $suggestion['position'] = $position++;
                    $suggestion['quota'] = isset($form_data['quota_' . $i]) && (int)$form_data['quota_' . $i] !== 0 ? (int)$form_data['quota_' . $i] : 0;
                }
                else 
                    continue;

                $suggestions[] = $suggestion;
            }
    
            $newquestion['suggestions'] = $suggestions;
            $current_data[] = $newquestion;
            
            if ($position < 1)
                throw new Exception('Veuillez entrer des suggestions');
    
            $this->eventModule->setData(json_encode($current_data));
            $this->em->persist($this->eventModule);
            $this->em->flush();
    
            return $this->getMainPage();
        } catch (Exception $e) {
            return $this->render('newquestion', ['error' => $e->getMessage()]);
        }
    }

    public function setNewAnswer() {
        $form_data = json_decode($this->request->getContent(), true);
        $current_data = json_decode($this->eventModule->getData(), true);
        $question_pos = (int)$form_data['survey_id'];

        for($i = 0; $i < count($current_data); $i++) {
            if ($i != $question_pos)
                continue;
            
            $question = $current_data[$i];

            for($j = 0; $j < count($question['suggestions']); $j++) {
                $suggestion = $question['suggestions'][$j];

                if (
                    (isset($form_data['suggestion_' . $j]) && $question['multiple']) || 
                    (isset($form_data['selected_suggestion']) && $j === (int)$form_data['selected_suggestion'] && !$question['multiple'])
                ) {
                    // this suggestion has been checked by user
                    if (!in_array($this->user->getId(), $suggestion['participants']))
                        $suggestion['participants'][] = $this->user->getId();
                } 
                else {
                    // this suggestion hasn't been checked by user
                    $tpm = [];
                    foreach($suggestion['participants'] as $id_participant)
                        if ($id_participant != $this->user->getId())
                            $tpm[] = $id_participant;
                    $suggestion['participants'] = $tpm;
                }

                $question['suggestions'][$j] = $suggestion;
            }
            $current_data[$i] = $question;
        }

        $this->eventModule->setData(json_encode($current_data));
        $this->em->persist($this->eventModule);
        $this->em->flush();

        return $this->getMainPage();
    }

    public function setDeleteQuestion() {
        $form_data = json_decode($this->request->getContent(), true);
        $current_data = json_decode($this->eventModule->getData(), true);

        if(!isset($form_data['more']))
            return $this->getMainPage('Question introuvable. Impossible de supprimer.');

        $question_pos = (int) $form_data['more'];
        $new_datas = array();

        for($i = 0; $i < count($current_data); $i++)
            if ($i != $question_pos)
                $new_datas[] = $current_data[$i];                

        $this->eventModule->setData(json_encode($new_datas));
        $this->em->persist($this->eventModule);
        $this->em->flush();

        return $this->getMainPage();
    }
}