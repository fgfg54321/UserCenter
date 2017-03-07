<?php

namespace UserCenterBundle\Controller;

use UserCenterBundle\Entity\UserInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Userinfo controller.
 *
 */
class UserInfoController extends Controller
{

    /**
     * user login
     *
     */
    public function loginAction(Request $request)
    {
        $account  = $request->query->get('account');
        $password = $request->query->get('password');

        if($account != null && $password != null)
        {
            $em = $this->getDoctrine()->getManager();

            $repository = $em->getRepository('UserCenterBundle:UserInfo');

            $query = $repository->createQueryBuilder('u')
                ->where('u.account = :account')
                ->setParameter('account', $account)
                ->getQuery();

            $userInfos = $query->getResult();
            $count     = count($userInfos);
            if($count > 0 && $userInfos[0] != null)
            {
                $userInfo = $userInfos[0];
                $postPwd = md5($password);
                $dbPwd   = $userInfo->getPassword();
                if(strcmp($postPwd,$dbPwd) == 0)
                {
                    $result = $this->ToJson($userInfo);
                }
                else
                {
                    $lState = new LoginState(null,0,-3,"account $account password is wrong");
                    $result = $lState->ToJson();
                }
            }
            else
            {
                $lState = new LoginState(null,0,-2,"account $account is not exist");
                $result = $lState->ToJson();
            }
        }
        else
        {
            $lState = new LoginState(null,0,-1,"account or password is empty");
            $result = $lState->ToJson();
        }

        return $this->render('userinfo/login.html.twig',
            array
            (
            'result' => $result,
            )
        );
    }


    /**
     * user register
     *
     */
    public function registerAction(Request $request)
    {
        $result   = "";
        $account  = $request->query->get('account');
        $password = $request->query->get('password');
        $name     = $request->query->get('name');

        if($account != null && $password != null && $name != null)
        {
            $em = $this->getDoctrine()->getManager();

            $repository = $em->getRepository('UserCenterBundle:UserInfo');

            $query = $repository->createQueryBuilder('u')
                ->where('u.account = :account')
                ->setParameter('account', $account)
                ->getQuery();

            $userInfos = $query->getResult();
            $count     = count($userInfos);
            if($count == 0)
            {
                $userInfo = new Userinfo();
                $userInfo->setAccount($account);
                $userInfo->setName($name);
                $userInfo->setPassword($password);
                $userInfo->setStatus("[]");
                $userInfo->setRegisterDate(date('Y-m-d H:i:s'));
                $userInfo->setUid();

                $em->persist($userInfo);
                $em->flush($userInfo);

                $rstate = new RegisterState($userInfo,1,0,"success");
                $result =  $rstate->ToJson();

            }
            else
            {
                $rstate = new RegisterState(null,0,-1,"account $account already register");
                $result = $rstate->ToJson();
            }
        }
        else
        {
            $rstate = new RegisterState(null,0,-2,"account or password or name is not set");
            $result = $rstate->ToJson();
        }

        return $this->render('userinfo/register.html.twig',
            array
            (
            'result' => $result,
            )
        );
    }

    /**
     * Lists all userInfo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userInfos = $em->getRepository('UserCenterBundle:UserInfo')->findAll();

        return $this->render('userinfo/index.html.twig', array(
            'userInfos' => $userInfos,
        ));
    }

    /**
     * Creates a new userInfo entity.
     *
     */
    public function newAction(Request $request)
    {
        $userInfo = new Userinfo();
        $form = $this->createForm('UserCenterBundle\Form\UserInfoType', $userInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userInfo);
            $em->flush($userInfo);

            return $this->redirectToRoute('userinfo_show', array('id' => $userInfo->getId()));
        }

        return $this->render('userinfo/new.html.twig', array(
            'userInfo' => $userInfo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a userInfo entity.
     *
     */
    public function showAction(UserInfo $userInfo)
    {
        $deleteForm = $this->createDeleteForm($userInfo);

        return $this->render('userinfo/show.html.twig', array(
            'userInfo' => $userInfo,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing userInfo entity.
     *
     */
    public function editAction(Request $request, UserInfo $userInfo)
    {
        $deleteForm = $this->createDeleteForm($userInfo);
        $editForm = $this->createForm('UserCenterBundle\Form\UserInfoType', $userInfo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('userinfo_edit', array('id' => $userInfo->getId()));
        }

        return $this->render('userinfo/edit.html.twig', array(
            'userInfo' => $userInfo,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a userInfo entity.
     *
     */
    public function deleteAction(Request $request, UserInfo $userInfo)
    {
        $form = $this->createDeleteForm($userInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userInfo);
            $em->flush($userInfo);
        }

        return $this->redirectToRoute('userinfo_index');
    }

    /**
     * Creates a form to delete a userInfo entity.
     *
     * @param UserInfo $userInfo The userInfo entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UserInfo $userInfo)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('userinfo_delete', array('id' => $userInfo->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function ToJson(UserInfo $userInfo)
    {
        $loginState = new LoginState($userInfo,1,0,"success");
        return  json_encode($loginState);
    }

    private function GetToken(UserInfo $userInfo)
    {
        $loginState = new LoginState($userInfo,1,0,"success");
        return $loginState->data['token'];
    }

}

class GLOBALVAR
{
    public static $TOKEN_KEY         = "123456";
    public static $EXPIRESTIME       = 3000;
    public static $DEFAULT_AUTHORITY = "user";
}

class LoginState
{
    public $state;
    public $error;
    public $msg;
    public $data  = array();

    function __construct(UserInfo $userInfo,$state,$error,$msg)
    {
        if($userInfo != null)
        {
            $this->data['account']           = $userInfo->getAccount();
            $this->data['name']              = $userInfo->getName();
            $this->data['registerDate']     = $userInfo->getRegisterDate();
            $this->data['deadline']          = date('Y-m-d H:i:s',strtotime($userInfo->getLastLogin()) + GLOBALVAR::$EXPIRESTIME);
            $this->data['status']            = $userInfo->getStatus();

            $token                = array();
            $token['name']       = $userInfo->getName();
            $token['password']  = $userInfo->getPassword();
            $token['deadline']  = $this->data['deadline'];

            $this->data['token']  =  JWT::encode($token, GLOBALVAR::$TOKEN_KEY);
        }
        else
        {
            $this->data = (object)$this->data;
        }

        $this->state = $state;
        $this->error = $error;
        $this->msg   = $msg;

    }

    public function ToJson()
    {
        return json_encode($this);
    }
}


class RegisterState
{
    public $state;
    public $error;
    public $msg;
    public $data  = array();

    function __construct(UserInfo $userInfo,$state,$error,$msg)
    {
        if($userInfo != null)
        {
            $this->data['account']         = $userInfo->getAccount();
            $this->data['password']        = $userInfo->getPassword();
            $this->data['name']             = $userInfo->getName();
            $this->data['registerDate']   = $userInfo->getRegisterDate();
            $this->data['status']          = $userInfo->getStatus();
        }
        else
        {
            $this->data = (object)$this->data;
        }

        $this->state = $state;
        $this->error = $error;
        $this->msg   = $msg;
    }

    public function ToJson()
    {
        return json_encode($this);
    }
}
