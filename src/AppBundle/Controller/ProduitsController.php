<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Categories;
use AppBundle\Entity\Produits;
use AppBundle\Form\RechercheProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ProduitsController extends Controller
{

    /**
     * @Route("/", name="page_produits")
     * @Route("/categorie/{categorie}", name="page_produitsByCategory")
     * @param Categories|null $categorie
     * @param Request $request
     * @return Response
     */
    public function indexAction(Categories $categorie = null,Request $request)
    {
        $session= new Session();

        if ($categorie == null)
            $findProduits=$this->getDoctrine()->getRepository('Produits')->findBy(array('disponible' => 1));
        else
            $findProduits=$this->getDoctrine()->getRepository('Produits')->produitsByCategorie($categorie) ;

       // if(!$findProduits) throw $this->createNotFoundException('La page n\'existe pas ');

        if ($session->has('panier')) {
            $panier = $session->get('panier');
        }else{
            $panier=false;
        }


        $produits =  $this->get('knp_paginator')->paginate(
            $findProduits, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            6/*limit per page*/
        );

        return $this->render('@Ecommerce/Produits/produits.html.twig',array('produits'=>$produits,'panier'=>$panier));

    }

    /**
     * @Route("/produit/{id}", name="page_produit")
     */
    public function produitAction($id)
    {
        $session= new Session();

        $produit=$this->getDoctrine()->getRepository('Produits')->find($id);

        if(!$produit) throw $this->createNotFoundException('La page n\'existe pas ');

        if ($session->has('panier')) {
            $panier = $session->get('panier');
        }else{

            $panier=false;
        }

        return $this->render('@Ecommerce/Produits/produit.html.twig',array('produit'=>$produit,'panier'=>$panier));
    }

    /**
     * @Route("/aze/", name="page_recherche")
     */
    public function rechercheProduitAction()
    {
        $form = $this->createForm(RechercheProduitType::class);
        $formView=$form->createView();

        return $this->render('@Ecommerce/Recherche/recherche.html.twig',array('form'=>$formView));
    }

    /**
     * @Route("/rechercheProduit/", name="page_rechercheTraitement")
     */
    public function rechercheTraitementAction(Request $request)
    {
        $form = $this->createForm(RechercheProduitType::class);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $chaine= $form['recherche']->getData();
        }else
        {
            throw $this->createNotFoundException('La page n\'existe pas ');
        }

        $findProduits=$this->getDoctrine()->getRepository('Produits')->rechercheProduit($chaine);

        if(!$findProduits) throw $this->createNotFoundException('La page n\'existe pas ');
        // var_dump($produits);

        $produits =  $this->get('knp_paginator')->paginate(
            $findProduits, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );

        return $this->render('@Ecommerce/Produits/produits.html.twig',array('produits'=>$produits));
    }

}
