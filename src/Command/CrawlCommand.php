<?php

namespace App\Command;

use App\Entity\Device;
use App\Json\JsonResponse;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InfluxDB2\Client as InfluxClient;
use InfluxDB2\Point;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand('app:crawl', description: 'Liest die Werte für alle Geräte aus und schreibt sie in die InfluxDB')]
class CrawlCommand extends Command {

    public const Endpoint = 'getData';

    public function __construct(private readonly EntityManagerInterface $em,
                                private readonly SerializerInterface $serializer,
                                private readonly InfluxClient $influxdb, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $client = new Client([
            'timeout' => 2
        ]);

        $api = $this->influxdb->createWriteApi();

        foreach($this->em->getRepository(Device::class)->findAll() as $device) {
            if (empty($device->getIp())) {
                $io->section(sprintf('Bearbeite Gerät mit MAC %s (Raum: %s)', $device->getMac(), $device->getRoom()));
                $io->info('Gerät wurde noch nicht gefunden, überspringe');
                continue;
            }

            $io->section(sprintf('Bearbeite Gerät mit MAC %s (Raum: %s, letzte IP-Adresse: %s)', $device->getMac(), $device->getRoom(), $device->getIp()));
            try {
                $response = $client->get(sprintf('http://%s/%s', $device->getIp(), self::Endpoint));
                $json = $response->getBody();

                $result = $this->serializer->deserialize($json, JsonResponse::class, 'json');

                $device->setLastSeen(new DateTimeImmutable());
                $this->em->persist($device);
                $this->em->flush();

                $point = Point::measurement('sensor')
                    ->addTag('room', $device->getRoom())
                    ->addField('co2', intval($result->getCo2()))
                    ->addField('temp', intval($result->getT()))
                    ->addField('humidity', intval($result->getRh()));

                $api->write($point);
                $io->success('Daten erfolgreich in InfluxDB geschrieben');
            } catch (GuzzleException $e) {
                $io->error(sprintf('Fehler beim Abfragen der Daten: %s', $e->getMessage()));
            } catch (Exception $e) {
                $io->error(sprintf('Unbekannter Fehler: %s', $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}