import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

function eventCallback(updatedCookies) {
    if (typeof updatedCookies.detail.pixupWishlist !== 'undefined') {
        if(updatedCookies.detail.pixupWishlist === true){
            var date, expires, days;
            days = 30;
            date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            expires = "; expires="+date.toUTCString();
            document.cookie = "pixupWishlistAccepted=1"+expires+"; path=/";
        }else
            document.cookie = "pixupWishlistAccepted" + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }else{
        // Required Cookies are set --> doing this because the Wishlist is a Required Cookie
        // which have to initialisized here
        // the other code above is for the case that the cookie is no more required ( then this line have to be deleted )
        //document.cookie = "pixupWishlistAccepted=1";
    }
}
document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, eventCallback);
