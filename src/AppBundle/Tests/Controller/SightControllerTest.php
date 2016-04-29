<?php

namespace AppBundle\Tests\Controller;

use AppBundle\DBAL\Types\SightTicketType;
use AppBundle\Entity\Locality;
use AppBundle\Entity\SightType;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class SightControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client;

    /** @var ObjectManager */
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->client  = static::makeClient();
        $this->manager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->getFixtures();
    }

    public function testGetAllAction()
    {
        $this->client->request('GET', '/api/v1/sights?limit=10&offset=0');

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(200, $data['code']);
        $this->assertCount(6, $data['sights']);
        $this->comparisonSight($data['sights'][0]);
    }

    public function testGetAction()
    {
        $this->client->request('GET', '/api/v1/sights/kamianets-podіlska-fortess');

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(200, $data['code']);
        $this->comparisonSight($data['sight']);
    }

    public function testGetTicketAction()
    {
        $this->client->request('GET', '/api/v1/sights/kamianets-podіlska-fortess/tickets');

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(200, $data['code']);
        $this->comparisonSightTicket($data['sight_tickets'][0]);
    }

    public function testGetTourAction()
    {
        $this->client->request('GET', '/api/v1/sights/kamianets-podіlska-fortess/tours');

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(200, $data['code']);
        $this->comparisonSightTour($data['sight_tours'][0]);
    }

    public function testCreateAction()
    {
        /** @var SightType $sightType */
        /** @var Locality $locality */
        $sightType = $this->manager->getRepository('AppBundle:SightType')->findSightTypeFirstResult();
        $locality  = $this->manager->getRepository('AppBundle:Locality')->findLocalityFirstResult();

        $data = [
            'name'       => 'Кам\'яна фортеця',
            'phone'      => '(03849)2-55-33',
            'website'    => 'http://muzeum.in.ua/',
            'longitude'  => 26.563411,
            'latitude'   => 48.67351,
            'sight_type' => $sightType->getId(),
            'locality'   => $locality->getId(),
        ];

        $this->client->request(
            'POST',
            '/api/v1/sights',
            $data,
            [],
            ['Content-Type' => 'application/json'],
            []
        );

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);
        $this->assertEquals(201, $data['code']);

        foreach ($data as $key => $element) {
            $this->assertEquals($element, $data[$key]);
        }
    }

    public function testUpdateAction()
    {
        /** @var SightType $sightType */
        /** @var Locality $locality */
        $sightType = $this->manager->getRepository('AppBundle:SightType')->findSightTypeFirstResult();
        $locality  = $this->manager->getRepository('AppBundle:Locality')->findLocalityFirstResult();

        $data = [
            'name'       => 'Кам\'яна фоssdfртеця',
            'phone'      => '(03849)2-55-4433',
            'website'    => 'http://muzeumvv.in.ua/',
            'longitude'  => 26.56,
            'latitude'   => 48.61,
            'sight_type' => $sightType->getId(),
            'locality'   => $locality->getId(),
        ];

        $this->client->request(
            'PUT',
            '/api/v1/sights/hotinska-fortress',
            $data,
            [],
            ['Content-Type' => 'application/json'],
            []
        );

        $response = $this->client->getResponse();
        $data     = json_decode($response->getContent(), true);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(200, $data['code']);

        foreach ($data as $key => $element) {
            $this->assertEquals($element, $data[$key]);
        }
    }

    public function testDeleteAction()
    {
        $this->client->request('DELETE', '/api/v1/sights/hortitsa');

        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
    }

    /**
     * Load fixtures for tests
     */
    private function getFixtures()
    {
        $fixtures = [
            'AppBundle\DataFixtures\ORM\LoadCountryData',
            'AppBundle\DataFixtures\ORM\LoadLocalityData',
            'AppBundle\DataFixtures\ORM\LoadSightTypeData',
            'AppBundle\DataFixtures\ORM\LoadSightData',
            'AppBundle\DataFixtures\ORM\LoadSightTourData',
            'AppBundle\DataFixtures\ORM\LoadSightTicketData',
        ];

        $this->loadFixtures($fixtures);
    }

    private function comparisonSight(array $data)
    {
        $sight = [
            'name'      => 'Кам\'янець-подільська фортеця',
            'phone'     => '(03849)2-55-33',
            'website'   => 'http://muzeum.in.ua/',
            'longitude' => 26.563411,
            'latitude'  => 48.67351,
            'slug'      => 'kamianets-podіlska-fortess',
        ];

        foreach ($sight as $key => $el) {
            $this->assertEquals($el, $data[$key]);
        }

        $this->assertEquals('Замок', $data['sight_type']['name']);
        $this->assertEquals('Кам\'янець-Подільський', $data['locality']['name']);
    }

    private function comparisonSightTour(array $data)
    {
        $sightTour = [
            'name'         => 'Екскурсійна програма по місту Кам’янець-Подільському',
            'company_name' => '7 днів',
            'tour_link'    => 'http://www.7dniv.ua/ua/tourism-directions',
            'slug'         => 'sightseeing-in-the-city-kamenetz-podolsk',
        ];

        foreach ($sightTour as $key => $el) {
            $this->assertEquals($el, $data[$key]);
        }
    }

    private function comparisonSightTicket(array $data)
    {
        $sightTour = [
            'type'     => SightTicketType::TRAIN_TICKET,
            'link_buy' => 'https://gd.tickets.ua/uk/railwaytracker/table/Kamenetz-Podolsk~2200260',
            'slug'     => 'kiev-kamyanets-train-ticket',
        ];

        foreach ($sightTour as $key => $el) {
            $this->assertEquals($el, $data[$key]);
        }
    }
}
