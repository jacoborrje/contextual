<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-21
 * Time: 19:26
 */

namespace App\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class TypeFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event)
    {
        $relationship = $event->getData();
        $form = $event->getForm();

        $choice_keys = ["parent/child", "husband/wife", "brother/sister", "student", "teacher", "business partner"];

        if (null != $event->getData()) {
            if($relationship->getFirstActor()) {
                $myself = $relationship->getActor1();
                $relative = $relationship->getActor2();
                $label = $relationship->getActor2();

            }
            else{
                $myself = $relationship->getActor2();
                $relative = $relationship->getActor1();
                $label = $relationship->getActor1();
            }
            if($relative->getBirthDate()>$myself->getBirthDate()){
                if($relative->getGender())
                    $choice_keys[0] = "daughter";
                else
                    $choice_keys[0] = "son";
            }
            else{
                if($relative->getGender())
                    $choice_keys[0] = "mother";
                else
                    $choice_keys[0] = "father";
            }
            if($relative->getGender()){
                $choice_keys[1] = "wife";
                $choice_keys[2] = "sister";
            }
            else{
                $choice_keys[1] = "husband";
                $choice_keys[2] = "brother";
            }
        }
        else{
            $label = "Type";
        }
        $choices = array(
            $choice_keys[0] => 1,
            $choice_keys[1] => 2,
            $choice_keys[2] => 3,
            $choice_keys[3] => 4,
            $choice_keys[4] => 5,
            $choice_keys[5] => 6
        );

        $form->add('type', ChoiceType::class, array(
            "choices" => $choices,
            "label" => $label
        ));
    }
}