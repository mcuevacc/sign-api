<?php

namespace ControllerBundle\Controller\MyAccount;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ServiceBundle\Service\Util\Constante;

class ContactController extends Controller
{   
    public function listAction(Request $request) {
        try{
            $decoded = $this->get('Jwt')->decodeToken($request->headers->get('authToken'));
            if(!$decoded['success']) return $this->json($decoded,Constante::$enumTock);
            $user = $decoded['user'];

            $contacts = $this->getDoctrine()->getRepository('EntityBundle:User\Contact')->findOneByUser($user->getId());

            $aContact = !$contacts ? [] : $contacts->getAContact();

            return  $this->json(['success'=>true,'entidad'=>$aContact]);

        }catch (\Exception $e){
            return $this->json([
                                    'success'=>false,
                                    'msg'=>$e->getMessage()
                                    ],Constante::$enumCodigo);
        }
    }

    public function readAction(Request $request, $id=null) {
        try{
            $decoded = $this->get('Jwt')->decodeToken($request->headers->get('authToken'));
            if(!$decoded['success']) return $this->json($decoded,Constante::$enumTock);
            $user = $decoded['user'];

            $contacts = $this->getDoctrine()->getRepository('EntityBundle:User\Contact')->findOneByUser($user->getId()); 

            $aContact = !$contacts ? [] : $contacts->getAContact();

            foreach($aContact as $contact){
                if( $id>$contact['id'] )
                    continue;
                else if( $id==$contact['id'] )
                    $readContact = $contact;
                break;
            }

            if( !isset($readContact) ) 
                return $this->json(['success'=>false,'msg'=>'elemento no encontrado'],Constante::$enumCodigo);

            return  $this->json(['success'=>true,'entidad'=>[$readContact]]);

        }catch (\Exception $e){
            return $this->json([
                                    'success'=>false,
                                    'msg'=>$e->getMessage()
                                    ],Constante::$enumCodigo);
        }
    }

    public function createAction(Request $request) {
        try{
            $decoded = $this->get('Jwt')->decodeToken($request->headers->get('authToken'));
            if(!$decoded['success']) return $this->json($decoded,Constante::$enumTock);
            $user = $decoded['user'];

            $cadena = 'name,number';
            $sentencia = $this->get('Read')->getData($cadena);
            eval($sentencia);
            if(!$existen)
                return  $this->json ([
                                        'success'=>false,
                                        'msg'=>'faltan parametros'
                                        ],Constante::$enumPerm);

            $resp = $this->get('Util')->validateMobileNumber($number);
            if(!$resp['success'])
                return  $this->json($resp);

            $begin;
            $this->getDoctrine()->getConnection()->beginTransaction();

            $contacts = $this->getDoctrine()->getRepository('EntityBundle:User\Contact')->findOneByUser($user->getId());
            if(!$contacts){
                $cadena = 'user/User\User^'.$user->getId();
                $contacts = $this->get('Create')->create('User\Contact',['cadena'=>$cadena]);
                if(!$contacts['success']) return $this->json($contacts,Constante::$enumCodigo);
                $contacts = $contacts['entity']; 
            }
  
            $aContact = $contacts->getAContact();

            $id = count($aContact)>0 ? end($aContact)['id']+1 : 1;

            $aContact[] = ['id'=>$id,'name'=>$name,'number'=>$number];

            $cadena = 'aContact^'.json_encode($aContact);
            $contacts = $this->get('Update')->upEntity('User\Contact',['id'=>$user->getId(),'search'=>'user','cadena'=>$cadena]);
            if(!$contacts['success']){
                $this->getDoctrine()->getConnection()->rollBack();              
                return  $this->json($contacts,Constante::$enumCodigo);
            }

            $this->getDoctrine()->getConnection()->commit();
            return  $this->json(['success'=>true,'id'=>$id]);

        }catch (\Exception $e){
            if( isset($begin) )
                $this->getDoctrine()->getConnection()->rollBack();
            return $this->json([
                                    'success'=>false,
                                    'msg'=>$e->getMessage()
                                    ],Constante::$enumCodigo);
        }
    }
    
    public function updateAction(Request $request, $id=null) {
        try{
            $decoded = $this->get('Jwt')->decodeToken($request->headers->get('authToken'));
            if(!$decoded['success']) return $this->json($decoded,Constante::$enumTock);
            $user = $decoded['user'];

            $cadena = 'name,number';
            $sentencia = $this->get('Read')->getData($cadena);
            eval($sentencia);
            if(!$existen)
                return  $this->json ([
                                        'success'=>false,
                                        'msg'=>'faltan parametros'
                                        ],Constante::$enumPerm);

            $resp = $this->get('Util')->validateMobileNumber($number);
            if(!$resp['success'])
                return  $this->json($resp);

            $contacts = $this->getDoctrine()->getRepository('EntityBundle:User\Contact')->findOneByUser($user->getId());
            $aContact = !$contacts ? [] : $contacts->getAContact();

            foreach($aContact as &$contact){
                if( $id>$contact['id'] )
                    continue;
                else if( $id==$contact['id'] ){
                    $editContact = ['id'=>$id,'name'=>$name,'number'=>$number];
                    $contact = $editContact;
                }
                break;
            }
            if( !isset($editContact) )
                return $this->json(['success'=>false,'msg'=>'elemento no encontrado'],Constante::$enumCodigo);

            $begin;
            $this->getDoctrine()->getConnection()->beginTransaction();
                              
            $cadena = 'aContact^'.json_encode($aContact);
            $contacts = $this->get('Update')->upEntity('User\Contact',['id'=>$user->getId(),'search'=>'user','cadena'=>$cadena]);
            if(!$contacts['success']){
                $this->getDoctrine()->getConnection()->rollBack();              
                return  $this->json($contacts,Constante::$enumCodigo);
            }

            $this->getDoctrine()->getConnection()->commit();
            return  $this->json(['success'=>true]);

        }catch (\Exception $e){
            if( isset($begin) )
                $this->getDoctrine()->getConnection()->rollBack();
            return $this->json([
                                    'success'=>false,
                                    'msg'=>$e->getMessage()
                                    ],Constante::$enumCodigo);
        }
    }

    public function deleteAction(Request $request, $id=null) {
        try{
            $decoded = $this->get('Jwt')->decodeToken($request->headers->get('authToken'));
            if(!$decoded['success']) return $this->json($decoded,Constante::$enumTock);
            $user = $decoded['user'];

            $contacts = $this->getDoctrine()->getRepository('EntityBundle:User\Contact')->findOneByUser($user->getId()); 
            $aContact = !$contacts ? [] : $contacts->getAContact();

            foreach ($aContact as $i => $contact) {
                if( $id>$contact['id'] )
                    continue;
                else if( $id==$contact['id'] ){
                    $delete=true;
                    unset($aContact[$i]);
                    $aContact = array_values($aContact);
                }
                break;
            }
            if( !isset($delete) ) 
                return $this->json(['success'=>false,'msg'=>'elemento no encontrado'],Constante::$enumCodigo);

            $begin;
            $this->getDoctrine()->getConnection()->beginTransaction();
                              
            $cadena = 'aContact^'.json_encode($aContact);
            $contacts = $this->get('Update')->upEntity('User\Contact',['id'=>$user->getId(),'search'=>'user','cadena'=>$cadena]);
            if(!$contacts['success']){
                $this->getDoctrine()->getConnection()->rollBack();              
                return  $this->json($contacts,Constante::$enumCodigo);
            }

            $this->getDoctrine()->getConnection()->commit();
            return  $this->json(['success'=>true]);

        }catch (\Exception $e){
            if( isset($begin) )
                $this->getDoctrine()->getConnection()->rollBack();    
            return $this->json([
                                    'success'=>false,
                                    'msg'=>$e->getMessage()
                                    ],Constante::$enumCodigo);
        }
    }
}