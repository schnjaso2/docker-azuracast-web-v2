<?php
namespace App\Console\Command;

use App\Acl;
use App\Entity;
use Azura\Console\Command\CommandAbstract;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetAdministrator extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('azuracast:account:set-administrator')
            ->setDescription('Set the account specified as a global administrator.')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The user\'s e-mail address.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Set Administrator');

        /** @var EntityManager $em */
        $em = $this->get(EntityManager::class);

        $user_email = $input->getArgument('email');

        $user = $em->getRepository(Entity\User::class)
            ->findOneBy(['email' => $user_email ]);

        if ($user instanceof Entity\User) {
            $admin_role = $em->getRepository(Entity\Role::class)
                ->find(Entity\Role::SUPER_ADMINISTRATOR_ROLE_ID);

            /** @var Entity\Repository\RolePermissionRepository $perms_repo */
            $perms_repo = $em->getRepository(Entity\RolePermission::class);

            $perms_repo->setActionsForRole($admin_role, [
                'actions_global' => [
                    Acl::GLOBAL_ALL,
                ]
            ]);

            $user_roles = $user->getRoles();

            if (!$user_roles->contains($admin_role)) {
                $user_roles->add($admin_role);
            }

            $em->persist($user);
            $em->flush();

            $io->text(__('The account associated with e-mail address "%s" has been set as an administrator', $user->getEmail()));
            $io->newLine();
            return 0;
        }

        $io->error(__('Account not found.'));
        $io->newLine();
        return 1;
    }
}
