<?php declare(strict_types=1);

namespace Pixup\Wishlist\Model;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class WishlistCookieHandler
{
    const COOKIE_NAME = "pixupWishlist";
    const COOKIE_USAGE_PERMISSION_NAME = "pixupWishlistAccepted";
    const CHECK_FOR_COOKIE_PERMISSION = true; // true if the pixupWishlistCookie is no more Required
    const COOKIE_TIME = 360000;
    const SESSION_IDENTIFIER = "pixupWishlistSessionIdentifier";

    public function __call($method,$arguments){
        if($method === "getCookieName")
            $this->getCookieName();
        if(method_exists($this, $method))
            return ($this->preCookieHandling() !== true)?false:call_user_func_array(array($this,$method),$arguments);
        throw new MethodNotFoundException("Method Not found in class ".self::class."::".$method."()",self::class,$method,$arguments);
    }

    private function preCookieHandling() :bool{
        if(self::CHECK_FOR_COOKIE_PERMISSION){
            if($_COOKIE[self::COOKIE_USAGE_PERMISSION_NAME] !== 1)
                return false;
            else    //regenerate cookie ( because of the expire time )
                setcookie(self::COOKIE_USAGE_PERMISSION_NAME,1,time()+self::COOKIE_TIME);
        }
        return true;
    }

    /**
     * @return bool
     * @description checks if the cookie is accepted by the cxonsent manager
     */
    public function isCookieAccepted():bool{
        if(isset($_COOKIE[self::COOKIE_USAGE_PERMISSION_NAME]) && $_COOKIE[self::COOKIE_USAGE_PERMISSION_NAME] == 1)
            return true;
        return false;
    }

    public function getCookieName(){
        return self::COOKIE_NAME;
    }

    public function generateCookieId():string{
        if($this->isCookieAccepted())
            return bin2hex(random_bytes(16));
        return $this->getSessionId();
    }

    public function getSessionId() :string{
        if(isset($_SESSION[self::SESSION_IDENTIFIER])){
            $id = $_SESSION[self::SESSION_IDENTIFIER];
        }else{
            $_SESSION[self::SESSION_IDENTIFIER] = $_SESSION['_sf2_attributes']['sessionId'];
            $id = $_SESSION['_sf2_attributes']['sessionId'];
        }
        //check if $id is a valid UUID
        $id = (ctype_xdigit($id) && strlen($id)==32)?$id:substr(bin2hex($id),0,32);
        while(strlen($id)<32)
            $id.=0;
        return $id;
    }

    public function getCookieId() :?string{
        if(!$this->checkIfCookieExsists()){
            if(!$this->isCookieAccepted())
                return $this->getSessionId();
            return null;
        }
        $data = unserialize($_COOKIE[self::COOKIE_NAME], ["allowed_classes" => false]);
        if(empty($data))
            return null;
        return $data['cookieID'];
    }

    public function createWishlistCookie(string $salesChannelId, string $wishListId, string $cookieID):string{
        //create new Cookie
        if($this->isCookieAccepted()) {
            setcookie(self::COOKIE_NAME, serialize([
                'wishlist' => [
                    'id' => ($wishListId == null) ? $this->generateCookieId() : $wishListId,
                    'salesChannelId' => $salesChannelId,
                ],
                'cookieID' => $cookieID
            ]),
                time() + self::COOKIE_TIME,
                '/'
            );
            return $cookieID;
        }
        return $this->getSessionId();
    }

    /**
     * @return bool
     * @description checks if the cookie exists
     */
    public function checkIfCookieExsists() :bool{
        if(!isset($_COOKIE[self::COOKIE_NAME]))
            return false;
        return true;
    }
}
