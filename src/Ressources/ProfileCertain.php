<?php
namespace Wabel\CertainAPI\Ressources;

use Wabel\CertainAPI\Interfaces\CertainRessourceInterface;
use Wabel\CertainAPI\CertainRessourceAbstract;
use Wabel\CertainAPI\Exceptions\RessourceException;
/**
 * ProfileCertain about the Profile entity
 *
 * @author rbergina
 */
class ProfileCertain extends CertainRessourceAbstract implements CertainRessourceInterface
{
    public function getRessourceName(){
        return 'Profile';
    }
    public function getMandatoryFields()
    {
        return array();
    }

    /**
     * Return the Profile object
     * @param string $email
     * @return ProfileObj
     * @throws RessourceException
     */
    public function getProfileByEmail($email)
    {
        $resultCertain =  $this->getProfileCertainReturnByEmail($email);
        if($resultCertain->isSuccessFul()){
            $resultCertainProfile = $resultCertain->getResults();
            if($resultCertainProfile->size == 1){
                return $resultCertainProfile->profiles[0];

            } elseif($resultCertainProfile->size > 1){
                throw new RessourceException('Duplicate entries');
            } else{
                    return $resultCertain;
            }
        } else{
            return null;
        }

    }


    /**
     * Return with all the result from certain.
     * @param string $email
     * @return ProfileCertain
     */
    public function getProfileCertainReturnByEmail($email)
    {
        return $this->get(null, ['email'=> $email]);
    }


    /**
     * Return with all the result from certain.
     * @return ProfileCertain[]
     */
    public function getProfiles($params = [])
    {
        $request=  $this->get(null,$params);
        if($request->isSuccessFul()){
            $profileCertainResults = $request->getResults();
            return $profileCertainResults->profiles;
        }
        return null;
    }

}