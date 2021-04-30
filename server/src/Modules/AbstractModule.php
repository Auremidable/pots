<?php

namespace App\Modules;

use App\Entity\Module;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractModule {

    protected $em;
    protected $request;

    public $status = 200;
    public $name;
    public $title;
    public $picture;

    abstract public function getMainPage();

    public function __construct(EntityManager $em, Request $request = null, $method = null, $eventModule = null, $user = null) {
        $this->em = $em;
        $this->request = $request;
        $this->method = $method;
        $this->eventModule = $eventModule;
        $this->user = $user;
    }

    protected function render($template_name, $data = array()) {
        $tpl = __DIR__ . '/' . $this->name . '/templates';
        $loader = new \Twig\Loader\FilesystemLoader($tpl);
        $twig = new \Twig\Environment($loader);
        $commom_data = array(
            'request' => $this->request,
            'user' => $this->user
        );
        $final_data = array_merge($commom_data, $data);
        return $twig->render($template_name . '.twig', $final_data);
    }


    public function install() {
        if ($this->em->getRepository(Module::class)->findOneBy(["name" => $this->name]))
            return true;

        $module = new Module();
        $module->setName($this->name);
        $module->setTitle($this->title);
        $module->setPicture($this->picture);
        $module->setActive(1);

        $this->em->persist($module);
        $this->em->flush();
    }

    public function call() {
        $method = $this->method;
        return $this->$method();
    }
}