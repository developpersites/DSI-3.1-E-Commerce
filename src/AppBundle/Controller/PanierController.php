<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Produits;
use AppBundle\Entity\UtilisateursAdresses;
use AppBundle\Form\RechercheProduitType;
use AppBundle\Form\UtilisateursAdressesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;





class PanierController extends Controller
{
    /**
     * @Route("/panier", name="page_panier")
     */
    public function panierAction(Request $request)
    {
        $session= $request->getSession();

        if (!$session->has('panier')) {  $session->set('panier', array());  }
        $panier = $session->get('panier');

        $produits=$this->getDoctrine()->getRepository('Produits')->findArray(array_keys($panier));


        return $this->render('@Ecommerce/Panier/panier.html.twig',array('produits'=>$produits ,'panier'=>$panier));
    }


    /**
     * @Route("/menu", name="menu")
     */
    public function menuAction(Request $request)
    {
        $session= $request->getSession();

        if (!$session->has('panier')) {  $session->set('panier', array());  }
        $panier = $session->get('panier');

        $produits=$this->getDoctrine()->getRepository('Produits')->findArray(array_keys($panier));


        return $this->render('@Ecommerce/Panier/menu.html.twig',array('produits'=>$produits ,'panier'=>$panier));
    }

    /**
     * @Route("/ajouter/{id}", name="page_ajouterPanier")
     */
    public function ajouterAction($id,Request $request)
    {

        $session= $request->getSession();

        if (!$session->has('panier')) {
            $session->set('panier', array());
            $session->getFlashBag()->add('success', " Article ajouté avec succès");
        }
             $panier = $session->get('panier');


        if (array_key_exists($id, $panier)) {

            if ($request->query->get('qte') != null){
                $panier[$id] = $request->query->get('qte');
                $session->getFlashBag()->add('success', " Quantité modifié avec succès");
            }
        }else{
            if($request->query->get('qte') != null){
                $panier[$id] = $request->query->get('qte');


            }  else{
                $panier[$id]=1;
                $session->getFlashBag()->add('success', " Article ajouté avec succès");
            }
        }

            $session->set('panier',$panier);

         return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/supprimer/{id}", name="page_supprimerPanier")
     */
    public function supprimerAction($id)
    {

        $session= new Session();

        $panier = $session->get('panier');

        if (array_key_exists($id, $panier)) {

            unset($panier[$id]);
            $session->set('panier',$panier);
            $session->getFlashBag()->add('success', " Article supprimé avec succès ");
        }

        return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/supprimerAdress/{id}", name="page_supprimerAdresse")
     */
    public function supprimerAdressAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity=$em->getRepository('UtilisateursAdresses')->find($id);
        $utilisateur =$this->container->get('security.token_storage')->getToken()->getUser();

       // var_dump($this->container->get('security.token_storage')->getToken()->getUser());

        //var_dump($adress->getUtilisateur());

       if($utilisateur !=$entity->getUtilisateur() || !$entity ){

            return $this->redirectToRoute('page_livraison');
       }

        $em->remove($entity);
        $em->flush();

        return $this->redirectToRoute('page_livraison');
    }

    /**
     * @Route("/livraison", name="page_livraison")
     */
    public function livraisonAction(Request $request)
    {

        $utilisateursAdress = new UtilisateursAdresses();
        $session=new Session();
        $utilisateur=$this->container->get('security.token_storage')->getToken()->getUser();

        // On récupere le formulaire
        $form = $this->createForm(UtilisateursAdressesType::class,$utilisateursAdress);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $utilisateursAdress->setUtilisateur($utilisateur);
            $em->persist($utilisateursAdress);
            $em->flush();

            return $this->redirectToRoute('page_livraison');

        }

        return $this->render('@Ecommerce/Panier/livraison.html.twig',array('utilisateur'=>$utilisateur,'form'=>$form->createView()));
    }


    public function setLivraisonOnSession(Request $request){
        $session= $request->getSession();
        if (!$session->has('adress')) {  $session->set('adress', array());  }
        $adress = $session->get('adress');

        if($request->request->get('livraison') != null and $request->request->get('facturation') != null) {

            $adress['livraison'] = $request->request->get('livraison');
            $adress['facturation'] = $request->request->get('facturation');
        }
        else{

            return $this->redirectToRoute('page_validation');
        }


        $session->set('adress', $adress);
        return $this->redirectToRoute('page_validation');
    }

    /**
     * @Route("/validation", name="page_validation")
     */
    public function validationAction(Request $request)
    {


        if ($request->getMethod() == 'POST')
        {
            $this->setLivraisonOnSession($request);
        }

        $em = $this->getDoctrine()->getManager();
        $prepareCommande = $this->forward('Commandes:prepareCommande');
        $commande = $em->getRepository('Commandes')->find($prepareCommande->getContent());


        return $this->render('@Ecommerce/Panier/validation.html.twig', array('cmd' => $commande));

    
    }
}
