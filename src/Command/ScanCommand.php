<?php

namespace App\Command;

use App\Entity\Device;
use App\Entity\IpNetwork;
use App\Json\JsonResponse;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use IPLib\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand('app:scan', description: 'Scannt das Netzwerk nach IP-Adressen')]
class ScanCommand extends Command {

    public const Endpoint = 'getData';

    public function __construct(private readonly EntityManagerInterface $em, private readonly SerializerInterface $serializer, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $client = new Client([
            'timeout' => 1
        ]);


        foreach($this->em->getRepository(IpNetwork::class)->findAll() as $network) {
            $io->section(sprintf('Scanne %s (%s)', $network->getName(), $network->getCidr()));

            $range = Factory::parseRangeString($network->getCidr());
            for($idx = 0; $idx < $range->getSize(); $idx++) {
                $ipAddress = $range->getAddressAtOffset($idx);

                if($ipAddress === null) {
                    continue;
                }

                $io->text(sprintf('Prüfe %s (%d)', $ipAddress->toString(), $ipAddress->getAddressType()));
                try {
                    $response = $client->get(sprintf('http://%s/%s', $ipAddress->toString(), self::Endpoint));
                    $json = $response->getBody();

                    $result = $this->serializer->deserialize($json, JsonResponse::class, 'json');
                    list($mac, $deviceName) = explode('-', $result->getDevice());

                    $device = $this->em->getRepository(Device::class)->findOneBy([
                        'mac' => $mac
                    ]);

                    if($device !== null) {
                        $device->setIp($ipAddress->toString());
                        $device->setLastSeen(new DateTimeImmutable());
                        $this->em->persist($device);
                        $this->em->flush();
                        $io->info(sprintf('IP-Adresse für das Gerät mit der MAC-Adresse %s (%s) aktualisiert', $mac, $device->getRoom()));
                    } else {
                        $io->info(sprintf('Gerät mit MAC-Adresse %s nicht in der Datenbank', $mac));
                    }
                } catch (Exception $e) {
                    $io->info('Kein Gerät gefunden');
                }
            }
        }

        return Command::SUCCESS;
    }
}