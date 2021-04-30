<?php

namespace App\Entity;

use App\Entity\Event;
use App\Entity\Module;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventModuleRepository;

/**
 * @ORM\Entity(repositoryClass=EventModuleRepository::class)
 */
class EventModule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="modules", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_event;

    /**
     * @ORM\ManyToOne(targetEntity=Module::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_module;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getIdEvent(): ?Event
    {
        return $this->id_event;
    }

    public function setIdEvent(?Event $id_event): self
    {
        $this->id_event = $id_event;

        return $this;
    }

    public function getIdModule(): ?Module
    {
        return $this->id_module;
    }

    public function setIdModule(?Module $id_module): self
    {
        $this->id_module = $id_module;

        return $this;
    }
}
