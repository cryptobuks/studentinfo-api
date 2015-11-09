<?php

namespace StudentInfo\Repositories;

use Doctrine\ORM\EntityRepository;

class DoctrineClassroomRepository extends EntityRepository implements ClassroomRepositoryInterface
{
    public function create($object)
    {
        $this->_em->persist($object);
        $this->_em->flush($object);
    }

    public function all()
    {
        $query = $this->_em->createQuery('SELECT c FROM StudentInfo\Models\Classroom c');
        return $query->getArrayResult();
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

    public function findClassroomByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}