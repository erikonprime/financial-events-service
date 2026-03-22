<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;

class AccountsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    #[DataProvider('dataTestGetBalance')]
    public function testGetBalance(string $account, array $expectedResult): void
    {
        $url = sprintf('/accounts/%s/balance', $account);
        $this->client->request('GET', $url);
        $response = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($account, $response['account'] ?? '');

        $actualBalances = [];
        foreach ($response['result'] as $item) {
            $actualBalances[$item['currency']] = $item['balance'];
        }

        foreach ($expectedResult as $currency => $expectedData) {
            self::assertArrayHasKey(
                $currency,
                $actualBalances,
                sprintf('Balance for currency "%s" not found in response.', $currency)
            );
            self::assertEquals(
                $expectedData['balance'],
                $actualBalances[$currency],
                sprintf('Balance mismatch for currency "%s".', $currency)
            );
        }
    }

    public static function dataTestGetBalance(): array
    {
        return [
            [
                'user_account',
                [
                    'CAD' => ['currency' => 'CAD', 'balance' => '-78.25'],
                    'EUR' => ['currency' => 'EUR', 'balance' => '-288.27'],
                    'GBP' => ['currency' => 'GBP', 'balance' => '-292.00'],
                ]
            ],
            [
                'system_cash_account',
                [
                    'CAD' => ['currency' => 'CAD', 'balance' => '102.45'],
                    'EUR' => ['currency' => 'EUR', 'balance' => '32.01'],
                    'GBP' => ['currency' => 'GBP', 'balance' => '513.67'],
                ]
            ],
            [
                'fee_account',
                [
                    'EUR' => ['currency' => 'EUR', 'balance' => '256.26'],
                ]
            ],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine')->getConnection();

        static::insertPaymentReceived($connection);
        static::insertPaymentSent($connection);
        static::insertFeeCharged($connection);
        parent::tearDownAfterClass();
    }

    public static function tearDownAfterClass(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('accounting_transaction', true));
        $connection->executeStatement($platform->getTruncateTableSQL('event_processed', true));
        $entityManager->close();

        parent::tearDownAfterClass();
    }

    private static function insertPaymentReceived(Connection $connection): void
    {
        // EUR
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_001',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '100.00',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'credit',
            'amount' => '100.00',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        // GBP
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_002',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '203.00',
            'currency' => 'GBP',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'credit',
            'amount' => '235.45',
            'currency' => 'GBP',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        //CAD
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_003',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '78.25',
            'currency' => 'CAD',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'credit',
            'amount' => '102.45',
            'currency' => 'CAD',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    private static function insertPaymentSent(Connection $connection): void
    {
        // EUR
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_004',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'debit',
            'amount' => '67.99',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'credit',
            'amount' => '67.99',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        // GBP
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_005',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '89.00',
            'currency' => 'GBP',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'credit',
            'amount' => '278.22',
            'currency' => 'GBP',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    private static function insertFeeCharged(Connection $connection): void
    {
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_006',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '256.26',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'fee_account',
            'direction' => 'credit',
            'amount' => '256.26',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
