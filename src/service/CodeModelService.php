<?php

namespace App\service;

use App\exception\code\InvalidCode;
use App\lib\Randomizer;
use App\model\CodeModel;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Respect\Validation\Rules\Date;

class CodeModelService extends Service
{

    public function save(CodeModel $code)
    {
        $this->entityManager->persist($code);
        $this->entityManager->flush($code);
    }

    public function createCode(\DateTime $time): CodeModel
    {

        $code = new CodeModel();
        $code->setCode(Randomizer::generateSixDigit());
        $code->setSessionId(session_id());
        $code->setCreatedAt(new \DateTime());
        $code->setExpiresAt($time);

        $this->save($code);

        return $code;
    }

    /**
     * @throws InvalidCode
     */
    public function isValid(string $sessionId, string $code, DateTime $time): bool
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $result = $qb->select('c')
            ->from(CodeModel::class, 'c')
            ->where('c.sessionId = :session')
            ->andWhere('c.code = :code')
            ->andWhere($qb->expr()->gte('c.expires_at', ':time',))
            ->setParameter('session', $sessionId)
            ->setParameter('code', $code)
            ->setParameter('time', $time)
            ->getQuery()
            ->getOneOrNullResult();

        if (!isset($result)) {
            throw new InvalidCode('Invalid Code');
        }

        return true;
    }

    public function findCodeBySessionID(string $sessionId): CodeModel|null
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $result = $qb->select('c')
            ->from(CodeModel::class, 'c')
            ->where('c.sessionId = :session')
            ->setParameter('session', $sessionId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;

    }

    public function hasExistingValidCode(string $sessionId,DateTime $date): bool
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $result = $qb->select('c')
            ->from(CodeModel::class, 'c')
            ->where('c.sessionId = :session')
            ->andWhere($qb->expr()->gte('c.expires_at', ':time',))
            ->setParameter('session', $sessionId)
            ->setParameter('time', $date)
            ->getQuery()
            ->getOneOrNullResult();

        return isset($result);

    }


}
