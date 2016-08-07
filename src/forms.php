<?php
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;

$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());

//create contact form
$forms = array();
$forms['contactForm'] = $app['form.factory']->createBuilder(FormType::class)
                        ->add(
                            'name',
                            TextType::class,
                            array(
                                'label'       => false,
                                'attr'        => array(
                                    'placeholder' => 'contact.placeholder.name',
                                    'pattern'     => '.{2,}', //minlength
                                    'class'       => 'col-sm-12',
                                ),
                                'constraints' => array(
                                    new Assert\NotBlank(
                                        array('message' => 'contact.name.not_blank')
                                    ),
                                    new Assert\Length(
                                        array(
                                            'min'        => 2,
                                            'minMessage' => 'contact.name.min_message',
                                        )
                                    ),
                                ),
                            )
                        )
                        ->add(
                            'email',
                            EmailType::class,
                            array(
                                'label'       => false,
                                'attr'        => array(
                                    'placeholder' => 'contact.placeholder.email',
                                    'class'       => 'col-sm-12',
                                ),
                                'constraints' => array(
                                    new Assert\NotBlank(
                                        array(
                                            'message' => 'contact.email.not_blank',
                                        )
                                    ),
                                    new Assert\Email(
                                        array(
                                            'message' => 'contact.email.valid',
                                        )
                                    ),
                                ),
                            )
                        )
                        ->add(
                            'subject',
                            TextType::class,
                            array(
                                'label'       => false,
                                'attr'        => array(
                                    'placeholder' => 'contact.placeholder.subject',
                                    'pattern'     => '.{3,}', //minlength
                                    'class'       => 'col-sm-12',
                                ),
                                'constraints' => array(
                                    new Assert\NotBlank(
                                        array(
                                            'message' => 'contact.subject.not_blank',
                                        )
                                    ),
                                    new Assert\Length(
                                        array(
                                            'min'        => 3,
                                            'minMessage' => 'contact.subject.min_message',
                                        )
                                    ),
                                ),
                            )
                        )
        //basic antispam
                        ->add(
            'dummy',
            TextType::class,
            array(
                'label'    => false,
                'required' => false,
                'attr'     => array(
                    'placeholder' => 'test',
                    'class'       => 'col-sm-12 hidden',
                ),
            )
        )
                        ->add(
                            'message',
                            TextareaType::class,
                            array(
                                'label'       => false,
                                'attr'        => array(
                                    'class'       => 'col-sm-12',
                                    'rows'        => 10,
                                    'placeholder' => 'contact.placeholder.message',
                                ),
                                'constraints' => array(
                                    new Assert\NotBlank(
                                        array(
                                            'message' => 'contact.message.not_blank',
                                        )
                                    ),
                                    new Assert\Length(
                                        array(
                                            'min'        => 5,
                                            'minMessage' => 'contact.message.min_message',
                                        )
                                    ),
                                ),
                            )
                        )
                        ->getForm();

return $forms;
