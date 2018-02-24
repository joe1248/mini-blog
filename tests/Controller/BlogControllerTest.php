<?php

namespace App\Tests\Controller;

use App\DataFixtures\ArticleFixtures;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    protected $kernelDir = '/app';

    /** @var ReferenceRepository */
    private $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                ArticleFixtures::class,
            ]
        )->getReferenceRepository();
    }

    public function testGetPageNotFound()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/PageNotFoundAnywhere');
        $this->assertStatusCode(200, $client);

        $this->assertEquals(
            '{"error":{"code":400,"message":"Page not found"}}',
            $client->getResponse()->getContent()
            );
    }

    public function testGetAllFirstPage()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/1/2');
        $this->assertStatusCode(200, $client);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(2, count($data['articles']));
        $this->assertEquals('/articles/2/2', $data['next']);
        $firstArticle = $data['articles'][0];

        $this->assertArrayHasKey('title', $firstArticle);
        $this->assertArrayHasKey('content', $firstArticle);
        $this->assertArrayHasKey('createdAt', $firstArticle);
        $this->assertEquals('2018-02-13 22:40:41', $firstArticle['createdAt']);
    }

    public function testGetAllSecondPage()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/2/2');
        $this->assertStatusCode(200, $client);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(2, count($data['articles']));
        $this->assertEquals('/articles/1/2', $data['prev']);
        $this->assertEquals('/articles/3/2', $data['next']);
    }

    public function testGetAllLastPage()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/3/2');
        $this->assertStatusCode(200, $client);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, count($data['articles']));
        $this->assertEquals('/articles/2/2', $data['prev']);
        $this->assertEquals(null, $data['next']);
    }

    /*
    public function testGetFeatured()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles');
        $this->assertStatusCode(200, $client);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(0, $data['offset']);
        $this->assertEquals(50, $data['limit']);
        $this->assertEquals(101, $data['total']);

        $articles = $data['articles'];
        $this->assertEquals(50, count($articles));
        // Test First and Last = 50th article data...
        $this->assertEquals(
            [
                'id' => $this->fixtures->getReference('article1')->getId(),
                'title' => 'Ikofurioico Oyabizak',
                'description' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cyrenaici quidem non recusant; Quae cum essent dicta, discessimus. At coluit ipse amicitias. De illis, cum volemus. Si longus, levis dictata sunt. Duo Reges: constructio interrete. Nulla erit controversia. Eadem nunc mea adversum te oratio est. \n\nSed ad illum redeo. Itaque hic ipse iam pridem est reiectus; Praeclare hoc quidem. Nihil sane. Quis non odit sordidos, vanos, leves, futtiles? Praeclare hoc quidem. \n\nHuius ego nunc auctoritatem sequens idem faciam. Qualem igitur hominem natura inchoavit? Compensabatur, inquit, cum summis doloribus laetitia. Memini vero, inquam; \n\nCur deinde Metrodori liberos commendas? Ratio quidem vestra sic cogit. Ut id aliis narrare gestiant? \n\nNihil illinc huc pervenit. Tubulo putas dicere? Restatis igitur vos; Perge porro; Paria sunt igitur. Quare conare, quaeso. \n\nBeatum, inquit. Ita nemo beato beatior. Honesta oratio, Socratica, Platonis etiam. Cur post Tarentum ad Archytam? Frater et T. \n\nIta prorsus, inquam; Cur post Tarentum ad Archytam? Haec dicuntur inconstantissime. Maximus dolor, inquit, brevis est. \n\nSi quicquam extra virtutem habeatur in bonis. Invidiosum nomen est, infame, suspectum. Polycratem Samium felicem appellabant. Ut pulsi recurrant? Etiam beatissimum? Sed quot homines, tot sententiae; \n\nApparet statim, quae sint officia, quae actiones. Hunc vos beatum; Nihilo magis. Omnia peccata paria dicitis. Quo tandem modo? \n\nQuis negat? Sed hoc sane concedamus. Estne, quaeso, inquam, sitienti in bibendo voluptas? An hoc usque quaque, aliter in vita? Si id dicis, vicimus. Quod ea non occurrentia fingunt, vincunt Aristonem; \n\n",
            ],
            $articles[0]
        );
        $this->assertEquals(
            [
                'id' => $this->fixtures->getReference('article280')->getId(),
                'title' => 'Uaelakopa Efoxuaavemof Afadigeb',
                'description' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quo modo autem philosophus loquitur? Is es profecto tu. Quorum altera prosunt, nocent altera. Duo Reges: constructio interrete. Quis istum dolorem timet? Contemnit enim disserendi elegantiam, confuse loquitur. \n\nIlle incendat? Contineo me ab exemplis. Egone quaeris, inquit, quid sentiam? Sedulo, inquam, faciam. \n\nSatis est ad hoc responsum. Quo tandem modo? Proclivi currit oratio. Ut id aliis narrare gestiant? At iam decimum annum in spelunca iacet. Sed nimis multa. \n\nQuare conare, quaeso. Immo alio genere; Contineo me ab exemplis. Non est ista, inquam, Piso, magna dissensio. \n\nScaevolam M. At enim hic etiam dolore. Quis enim redargueret? Hoc tu nunc in illo probas. \n\nAt ille pellit, qui permulcet sensum voluptate. Aliter enim explicari, quod quaeritur, non potest. Cur iustitia laudatur? Hoc tu nunc in illo probas. \n\nCollatio igitur ista te nihil iuvat. Non laboro, inquit, de nomine. \n\nNullus est igitur cuiusquam dies natalis. Apparet statim, quae sint officia, quae actiones. \n\nEstne, quaeso, inquam, sitienti in bibendo voluptas? Utilitatis causa amicitia est quaesita. Illud non continuo, ut aeque incontentae. Vide, quantum, inquam, fallare, Torquate. Nunc haec primum fortasse audientis servire debemus. \n\nEquidem e Cn. Id mihi magnum videtur. Hic nihil fuit, quod quaereremus. \n\n",
            ],
            $articles[49]
        );
    }

    public function testGetFeaturedWorksWithLimit()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/0/100');
        $this->assertStatusCode(200, $client);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $data['offset']);
        $this->assertEquals(100, $data['limit']);
        $this->assertEquals(101, $data['total']);
        $articles = $data['articles'];
        $this->assertEquals(100, count($articles));
    }

    public function testGetFeaturedWorksWithLimitAndOffset()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/75/100');
        $this->assertStatusCode(200, $client);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(75, $data['offset']);
        $this->assertEquals(100, $data['limit']);
        $this->assertEquals(101, $data['total']);
        $articles = $data['articles'];
        $this->assertEquals(26, count($articles));
    }

    public function testGetFeaturedFailsIfOffsetNotNumeric()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/bad_offset');
        $this->assertStatusCode(200, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request: invalid value specified for `offset`'
                ]
            ],
            $response
        );
    }

    public function testGetFeaturedFailsIfLimitNotNumeric()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/5/bad_limit');
        $this->assertStatusCode(200, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request: invalid value specified for `limit`'
                ]
            ],
            $response
        );
    }

    public function testGetFeaturedFailsIfOffsetNegative()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/-1');
        $this->assertStatusCode(200, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request: Invalid value specified for `offset`. Minimum required value is 0.'
                ]
            ],
            $response
        );
    }

    public function testGetFeaturedFailsIfLimitAboveMaximum()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/articles/5/101');
        $this->assertStatusCode(200, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request: Invalid value specified for `limit`. Maximum allowed value is 100.'
                ]
            ],
            $response
        );
    }*/
}