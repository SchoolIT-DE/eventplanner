<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupAdminsType;
use App\Form\GroupMembershipsType;
use App\Form\GroupType;
use App\Security\Voter\GroupVoter;
use SchoolIT\CommonBundle\Form\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ManageGroupsController extends Controller {

    /**
     * @Route("/admin/groups", name="manage_groups")
     */
    public function index() {
        /** @var Group[] $groups */
        $groups = $this->getDoctrine()
            ->getRepository(Group::class)
            ->findAllUserIsAdminOf($this->getUser());

        return $this->render('admin/groups/index.html.twig', [
            'groups' => $groups
        ]);
    }

    /**
     * @Route("/admin/groups/add", name="add_group")
     */
    public function add(Request $request) {
        $this->denyAccessUnlessGranted(GroupVoter::ADD);

        $group = new Group();

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $group->addMember($this->getUser());
            $group->addAdmin($this->getUser());

            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'manage_groups.add.success');

            return $this->redirectToRoute('manage_groups');
        }

        return $this->render('admin/groups/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/groups/{id}/edit", name="edit_group")
     */
    public function edit(Request $request, Group $group) {
        $this->denyAccessUnlessGranted(GroupVoter::EDIT, $group);

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'manage_groups.edit.success');

            return $this->redirectToRoute('manage_groups');
        }

        return $this->render('admin/groups/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/groups/{id}/admins", name="edit_group_admins")
     */
    public function admins(Request $request, Group $group) {
        $this->denyAccessUnlessGranted(GroupVoter::EDIT, $group);

        $form = $this->createForm(GroupAdminsType::class, $group);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'manage_groups.admins.success');

            return $this->redirectToRoute('manage_groups');
        }

        return $this->render('admin/groups/admins.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/groups/{id}/memberships", name="edit_group_memberships")
     */
    public function memberships(Request $request, Group $group) {
        $this->denyAccessUnlessGranted(GroupVoter::EDIT, $group);

        $form = $this->createForm(GroupMembershipsType::class, $group);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'manage_groups.memberships.success');

            return $this->redirectToRoute('manage_groups');
        }

        return $this->render('admin/groups/memberships.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/groups/{id}/remove", name="remove_group")
     */
    public function remove(Request $request, Group $group) {
        $this->denyAccessUnlessGranted(GroupVoter::REMOVE, $group);

        $form = $this->createForm(ConfirmType::class, null, [
            'message' => $this->get('translator')->trans('manage_groups.remove.confirm', [
                '%name%' => $group->getName()
            ])
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();

            $this->addFlash('success', 'manage_groups.remove.success');
            return $this->redirectToRoute('manage_groups');
        }

        return $this->render('admin/groups/remove.html.twig', [
            'form' => $form->createView()
        ]);
    }

}