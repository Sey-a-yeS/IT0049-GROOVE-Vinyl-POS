<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetStaffPassword extends BaseCommand
{
    protected $group = 'GROOVE';
    protected $name = 'groove:staff-password';
    protected $description = 'Set a local staff password from the GROOVE_DEMO_PASSWORD environment variable.';
    protected $usage = 'groove:staff-password <username>';
    protected $arguments = ['username' => 'The existing staff username to update.'];

    public function run(array $params): void
    {
        $username = trim($params[0] ?? '');
        $password = (string) getenv('GROOVE_DEMO_PASSWORD');

        if ($username === '') {
            CLI::error('Provide an existing staff username.');
            return;
        }
        if (strlen($password) < 10) {
            CLI::error('Set GROOVE_DEMO_PASSWORD to a password of at least 10 characters for this command only.');
            return;
        }

        $model = new UserModel();
        $user = $model->where('username', $username)->first();
        if (! $user) {
            CLI::error('Staff account not found.');
            return;
        }

        $model->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        CLI::write('Password updated for ' . $username . '. The password was not stored in source code.', 'green');
    }
}
