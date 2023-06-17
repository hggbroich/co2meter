<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['mac'])]
class Device {

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $uuid;

    #[ORM\Column(type: 'string', length: 12, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('~^([0-9A-Fa-f]{12})$~')]
    private ?string $mac;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $room;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastSeen = null;

    /**
     * @return Uuid
     */
    public function getUuid(): Uuid {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getMac(): ?string {
        return $this->mac;
    }

    /**
     * @param string|null $mac
     */
    public function setMac(?string $mac): void {
        $this->mac = $mac;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip): void {
        $this->ip = $ip;
    }

    /**
     * @return string|null
     */
    public function getRoom(): ?string {
        return $this->room;
    }

    /**
     * @param string|null $room
     */
    public function setRoom(?string $room): void {
        $this->room = $room;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getLastSeen(): ?DateTimeImmutable {
        return $this->lastSeen;
    }

    /**
     * @param DateTimeImmutable|null $lastSeen
     */
    public function setLastSeen(?DateTimeImmutable $lastSeen): void {
        $this->lastSeen = $lastSeen;
    }
}