<?php


namespace StudentInfo\Repositories;


use Doctrine\ORM\EntityRepository;

class DoctrineLectureRepository extends EntityRepository implements LectureRepositoryInterface
{
    public function create($object)
    {
        $this->_em->persist($object);
        $this->_em->flush($object);
    }

    public function all()
    {
        return $query = $this->_em->createQuery('SELECT l FROM StudentInfo\Models\Lecture l')->getArrayResult();
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

    public function find($id)
    {
        return $this->_em->find('StudentInfo\Models\Lecture', $id);
    }
}