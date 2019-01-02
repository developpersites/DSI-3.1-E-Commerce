<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Commandes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Commande controller.
 *
 * @Route("admin/commandes")
 */
class CommandesAdminController extends Controller
{
    /**
     * Lists all commande entities.
     *
     * @Route("/", name="admin_commandes_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $commandes = $em->getRepository('Commandes')->findAll();

        return $this->render('Admin:commandesAdmin/index.html.twig', array(
            'commandes' => $commandes,
        ));
    }


    /**
     * Finds and displays a commande entity.
     *
     * @Route("/{id}", name="admin_factures_show")
     * @Method("GET")
     */
    public function showFacturesAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $facture = $em->getRepository('Commandes')->find($id);
        if (!$facture)
        {
            $this->get('session')->getFlashBag()->add('error', 'Une erreur est survenue lors du genération PDF');
            return $this->redirect($this->generateUrl('page_factures'));
        }
            $response= $this->container->get('setNewFacture')->facture($facture);

        return new Response($response->Output('facture .pdf'), 200, array('Content-Type' => 'application/pdf'));


    }
}
