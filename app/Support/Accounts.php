<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Creating a business account: the login, the account, the owner link and its
 * page row, in one transaction. Shared by the signup form and the free lead
 * form builder, so both produce exactly the same shape of account.
 */
final class Accounts
{
    public static function emailTaken(string $email): bool
    {
        return Database::first('SELECT id FROM users WHERE email = :e', ['e' => $email]) !== null;
    }

    /**
     * @param array{business:string,first:string,last:?string,email:string,password:string,plan:string,status?:string} $owner
     * @param array<string,mixed> $page Columns for the pages row.
     * @return array{user_id:int,account_id:int,slug:string}
     */
    public static function create(array $owner, array $page): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::run(
                'INSERT INTO users (email, password_hash, first_name, last_name, password_changed_at)
                 VALUES (:email, :hash, :first, :last, NOW())',
                [
                    'email' => $owner['email'],
                    'hash' => password_hash($owner['password'], PASSWORD_DEFAULT),
                    'first' => $owner['first'],
                    'last' => $owner['last'],
                ],
            );
            $userId = (int) $pdo->lastInsertId();

            $slug = Slug::unique($owner['business']);
            Database::run(
                'INSERT INTO accounts (business_name, slug, plan, requested_plan, page_status, signup_ip)
                 VALUES (:name, :slug, :plan, :requested, :status, :ip)',
                [
                    'name' => $owner['business'],
                    'slug' => $slug,
                    'plan' => Plans::FREE,
                    'requested' => Plans::isPaid($owner['plan']) ? $owner['plan'] : null,
                    'status' => $owner['status'] ?? 'intake',
                    'ip' => Request::ip(),
                ],
            );
            $accountId = (int) $pdo->lastInsertId();

            Database::run(
                "INSERT INTO account_users (account_id, user_id, role) VALUES (:a, :u, 'owner')",
                ['a' => $accountId, 'u' => $userId],
            );

            $columns = array_keys($page);
            Database::run(
                'INSERT INTO pages (account_id' . ($columns ? ', ' . implode(', ', $columns) : '') . ')
                 VALUES (:account_id' . ($columns ? ', :' . implode(', :', $columns) : '') . ')',
                ['account_id' => $accountId] + $page,
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['user_id' => $userId, 'account_id' => $accountId, 'slug' => $slug];
    }
}
