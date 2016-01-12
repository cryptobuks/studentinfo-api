<?php

namespace StudentInfo\Repositories;


use Doctrine\ORM\EntityRepository;

class DoctrineGroupEventRepository extends EntityRepository implements GroupEventRepositoryInterface
{
    public function create($object)
    {
        $this->_em->persist($object);
        $this->_em->flush($object);
    }

    public function all($faculty, $start = 0, $count = 20)
    {
        return $query = $this->_em->createQuery('SELECT g FROM StudentInfo\Models\GroupEvent g, StudentInfo\Models\Faculty f
              WHERE g.organisation = f.id AND f.slug =:faculty')
            ->setParameter('faculty', $faculty)
            ->setFirstResult($start)
            ->setMaxResults($count)
            ->getResult();
    }

    public function find($id)
    {
        return $this->_em->find('StudentInfo\Models\GroupEvent', $id);
    }

    public function destroy($object)
    {
        $this->_em->remove($object);
        $this->_em->flush($object);
    }

    public function update($object)
    {
        $this->_em->flush($object);
    }
}