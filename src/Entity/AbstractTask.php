<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractTask extends TenantAwareEntity
{
    #[ORM\Column(type: "string", length: 255)]
    protected string $name;
    #[ORM\Column(type: "string", length: 255, nullable: false)]
    protected string $type;
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    protected ?string $identifier;
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    protected ?string $processId;
    #[ORM\Column(type: "string", length: 100, nullable: false)]
    protected string $status;
    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $progress;
    #[ORM\Column(type: "json", nullable: true)]
    protected ?array $payload = [];
    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $output = null;
    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $error;
    #[ORM\Column(type: "boolean", nullable: true)]
    protected ?bool $warnings = false;
    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $startedAt = null;
    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $finishedAt = null;


    public function __toString()
    {
        return $this->getName() ?? 'Unnamed Task - ' . $this->getId();
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPayload(): array
    {
        return (array) $this->payload;
    }

    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): void
    {
        $this->output = $output;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    public function getWarnings(): ?bool
    {
        return $this->warnings;
    }

    public function setWarnings(?bool $warnings): void
    {
        $this->warnings = $warnings;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }


    public function appendError(?string $error): void
    {
        $this->error = $this->error . "\r\n" . $error;
    }


    public function getTaskDuration()
    {
        if ($this->getStartedAt() && $this->getFinishedAt()) {
            $duration = $this->getFinishedAt()->diff($this->getStartedAt());
            return $duration->format('%H:%I:%S'); // Assuming format HH:MM:SS
        }
        return '-';
    }

    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    public function setProcessId(?string $processId): void
    {
        $this->processId = $processId;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(?int $progress): void
    {
        $this->progress = $progress;
    }

    public function calculateProgress($completed, $total): void
    {
        $this->progress = $total > 0 ? round(($completed / $total) * 100) : 0;
    }

}
