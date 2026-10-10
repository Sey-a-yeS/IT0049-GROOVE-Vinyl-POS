<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetStaffPassword extends BaseCommand
{
    protected $group = 'GROOVE';
    protected $name = 'groove:staff-password';
    protected $description = 'Set one staff password from the temporary GROOVE_STAFF_PASSWORD environment variable.';
    protected $usage = 'groove:staff-password <username>';
    protected $arguments = ['username' => 'The existing staff username to update.'];

    public function run(array $params): void
    {
        $username = trim($params[0] ?? '');
        $password = (string) getenv('GROOVE_STAFF_PASSWORD');

        if ($username === '') {
            CLI::error('Provide an existing staff username.');
            return;
        }
        if (strlen($password) < 10) {
            CLI::error('Set GROOVE_STAFF_PASSWORD to a password of at least 10 characters for this command only.');
            return;
        }

        $model = new UserModel();
        $user = $model->where('username', $username)->where('is_archived', false)->first();
        if (! $user) {
            CLI::error('Active staff account not found.');
            return;
        }

        if (! $model->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)])) {
            CLI::error('The password could not be updated.');
            return;
        }

        CLI::write('Password updated for active staff account ' . $username . '. The secret was not printed or stored in source code.', 'green');
    }
}
