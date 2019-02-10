<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Source;

class SourceController extends AbstractController
{

    public function add($name,$address,$priority)
    {
        $source = new Source();

        $source->setName($name);
        $source->setAddress($address);
        $source->setPriority($priority);

        $content = [
            'id' => $source->getId(),
            'name' => $name,
            'address' => $address,
        ];

        if(!$this->isExist($source->getAddress())){

            $em = $this->getDoctrine()->getManager();
            $em->persist($source);
            $em->flush();

            $content['success'] = true;
            return $this->render('source/add.html.twig', $content);
        } else {
            $content['success'] = false;
            return $this->render('source/add.html.twig', $content);
        }
    }

    private function isExist($address)
    {
    $source = $this->getDoctrine()
        ->getRepository(Source::class)
        ->findOneBy(['address' => $address]);

    if ($source) {
        return true;
    } else {
        return false;
    }
}

    /**
     * @Route("/source/delete/{id}", name="deleteSource")
     */
    public function delete($id)
    {
    $source = $this->getDoctrine()
        ->getRepository(Source::class)
        ->find($id);
    $em = $this->getDoctrine()->getManager();
    $em->remove($source);
    $em->flush();
    }
}
