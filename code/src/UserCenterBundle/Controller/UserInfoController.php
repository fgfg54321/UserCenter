<?php

namespace UserCenterBundle\Controller;

use UserCenterBundle\Entity\UserInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
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

                    $loginTime = time();
                    $deadline  = $loginTime + GLOBALVAR::$EXPIRESTIME;

                    $userInfo->setLastLogin($loginTime);
                    $userInfo->setDeadline($deadline);
                    $this->createToken($userInfo);

                    $em->flush($userInfo);

                    $lState = new LoginState($userInfo,1,0,"success");

                }
                else
                {
                    $lState = new LoginState(null,0,-3,"account $account password is wrong");
                }
            }
            else
            {
                $lState = new LoginState(null,0,-2,"account $account is not exist");
            }
        }
        else
        {
            $lState = new LoginState(null,0,-1,"account or password is empty");
        }

        $jsonResponse = JsonResponse::create($lState, 200);

        return $jsonResponse;

    }


    /**
     * user register
     *
     */
    public function registerAction(Request $request)
    {
        $account     = $request->query->get('account');
        $password    = $request->query->get('password');
        $name        = $request->query->get('name');
        $productName = $request->query->get('productName','test');

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
                $userInfo->setPassword(md5($password));
                $userInfo->setRegisterDate(time());
                $userInfo->setProductName($productName);

                $em->persist($userInfo);
                $em->flush($userInfo);

                $rstate = new RegisterState($userInfo,1,0,"success");

            }
            else
            {
                $rstate = new RegisterState(null,0,-1,"account $account already register");
            }
        }
        else
        {
            $rstate = new RegisterState(null,0,-2,"account or password or name is not set");
        }

        $jsonResponse = JsonResponse::create($rstate, 200);

        return $jsonResponse;
    }

    /**
     * check user
     *
     */
    public function checkAction(Request $request)
    {
        $id       = $request->query->get('id');
        $tokenstr = $request->query->get('token');

        if($id != null && $tokenstr != null)
        {
            $em        = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('UserCenterBundle:UserInfo');

            $query = $repository->createQueryBuilder('u')
                ->where('u.id = :id')
                ->setParameter('id', $id)
                ->getQuery();

            $userInfos = $query->getResult();
            $count     = count($userInfos);
            if ($count > 0 && $userInfos[0] != null)
            {
                $userInfo  = $userInfos[0];
                $uTokenStr = $userInfo->getToken();

                if ($tokenstr == $uTokenStr)
                {
                    $token   = (new Parser())->parse((string)$tokenstr);
                    $expired = $token->isExpired();

                    if (!$expired)
                    {
                        $cState = new  CheckState($userInfo, 1, 0, "success");
                    }
                    else
                    {
                        $cState = new  CheckState(null, 0, -1, "token was expired!");
                    }
                }
                else
                {
                    $cState = new  CheckState(null, 0, -2, "token wrong!");
                }

            }
            else
            {
                $cState = new  CheckState(null, 0, -3, "user not exist!");
            }
        }
        else
        {
            $cState = new  CheckState(null, 0, -3, "id or token is not set");
        }
        $jsonResponse = JsonResponse::create($cState, 200);
        return $jsonResponse;
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

        if ($form->isSubmitted() && $form->isValid())
        {
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

        if ($editForm->isSubmitted() && $editForm->isValid())
        {
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

        if ($form->isSubmitted() && $form->isValid())
        {
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

    private  function createToken(UserInfo $userInfo)
    {
        $id                                = $userInfo->getId();
        $deadline                          = $userInfo->getDeadline();

        $builder = new Builder();
        $token   = $builder->setIssuer(GLOBALVAR::$ISUSER)
            ->setAudience(GLOBALVAR::$ISUSER)
            ->setId(GLOBALVAR::$TOKEN_HEADER,true)
            ->setIssuedAt(time())
            ->setExpiration($deadline)
            ->set('id',$id)
            ->getToken(); // Retrieves the generated token

        $tokenStr =  (string)$token;
        $userInfo->setToken($tokenStr);

        return $tokenStr;
    }


}

class GLOBALVAR
{
    public static $ISUSER            = "http://localhost.com";
    public static $TOKEN_HEADER      = "123456";
    public static $EXPIRESTIME       = 3000;
    public static $DEFAULT_AUTHORITY = "user";
}

class LoginState
{
    public $state;
    public $error;
    public $msg;
    public $data  = array();

    function __construct($userInfo,$state,$error,$msg)
    {
        if($userInfo != null)
        {
            $id                                = $userInfo->getId();
            $account                           = $userInfo->getAccount();
            $logintime                         = $userInfo->getLastLogin();
            $deadline                          = $userInfo->getDeadline();
            $status                            = $userInfo->getStatus();
            $regDate                           = $userInfo->getRegisterDate();
            $tokenstr                          = $userInfo->getToken();
            $productName                       = $userInfo->getProductName();

            $this->data['id']                = $id;
            $this->data['account']           = $account;
            $this->data['name']              = $userInfo->getName();
            $this->data['registerDate']     = date('Y-m-d H:i:s',$regDate);;
            $this->data['lastLogin']         = date('Y-m-d H:i:s',$logintime);
            $this->data['deadline']          = date('Y-m-d H:i:s',$deadline);
            $this->data['status']            = $status;
            $this->data['token']             = $tokenstr;
            $this->data['productNmae']      = $productName;

        }
        else
        {
            $this->data = (object)$this->data;
        }

        $this->state = $state;
        $this->error = $error;
        $this->msg    = $msg;

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

    function __construct($userInfo,$state,$error,$msg)
    {
        if($userInfo != null)
        {
            $this->data['account']         = $userInfo->getAccount();
            $this->data['password']        = $userInfo->getPassword();
            $this->data['name']             = $userInfo->getName();
            $this->data['registerDate']   = $userInfo->getRegisterDate();
            $this->data['status']          = $userInfo->getStatus();
            $this->data['productNmae']    = $userInfo->getProductName();
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

class CheckState
{
    public $state;
    public $error;
    public $msg;
    public $data  = array();

    function __construct($userInfo,$state,$error,$msg)
    {
        if($userInfo != null)
        {
            $id                                = $userInfo->getId();
            $account                           = $userInfo->getAccount();
            $logintime                         = $userInfo->getLastLogin();
            $deadline                          = $userInfo->getDeadline();
            $status                            = $userInfo->getStatus();
            $regDate                           = $userInfo->getRegisterDate();
            $productName                       = $userInfo->getProductName();

            $this->data['id']                = $id;
            $this->data['account']           = $account;
            $this->data['name']              = $userInfo->getName();
            $this->data['registerDate']     = date('Y-m-d H:i:s',$regDate);;
            $this->data['lastLogin']         = date('Y-m-d H:i:s',$logintime);
            $this->data['deadline']          = date('Y-m-d H:i:s',$deadline);
            $this->data['status']            = $status;
            $this->data['productNmae']      = $productName;
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