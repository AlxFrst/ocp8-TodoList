<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    protected UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, private Security $security, UserRepository $userRepository)
    {
        parent::__construct($registry, Task::class);
        $this->userRepository = $userRepository;

    }

    /**
     * Find Tasks by author And add Anonym tasks for admin
     * 
     * @return Task[] Returns an array of Task objects
     */
    public function findByRoleAndStatus(UserInterface $user, bool $statu): array
    {
        $query = $this->createQueryBuilder('task')
            ->setParameter('user', $user)
            ->setParameter('statu', $statu)
            ->andwhere('task.author = :user')
            ->andwhere('task.isDone = :statu');
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $userAnonym = $this->userRepository->findOneBy(['username'=> 'Anonyme']);
            $query->setParameter('userAnonym', $userAnonym)
                ->orwhere('task.author = :userAnonym');
        }
        return $query->getQuery()->getResult();
    }

//    /**
//     * @return Task[] Returns an array of Task objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
