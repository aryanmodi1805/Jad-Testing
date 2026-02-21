
import { initializeApp } from "firebase/app";

import { getMessaging, getToken,onMessage} from "firebase/messaging";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID
};

// // Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

requestPermission();
navigator.serviceWorker.register(new URL('./worker.js', import.meta.url),{type:'module', updateViaCache:'all'}).then((worker)=>{
    getToken(messaging, {vapidKey: import.meta.env.VITE_FIREBASE_VAPID, serviceWorkerRegistration: worker}).then((currentToken) => {
        if (currentToken) {
            saveToken(currentToken);
        }

    }).catch((err) => {
    });
});

onMessage(messaging, function(payload) {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register(new URL('./worker.js', import.meta.url),{type:'module', updateViaCache:'all'})
            .then(function (registration) {
                setTimeout(() => {
                    console.log('payload', payload);
                    registration.showNotification(payload.notification.title,{
                            body: payload.data.body,
                            icon: payload.data.icon,
                            image: payload.data.image,
                            actions: [{action: 'view',title: payload.data.action_title}],

                            onClick: function() {
                                console.log('Notification clicked');
                            },
                            dir: payload.data.dir,
                            data:{
                                url: payload.data.url
                            },
                            lang: payload.data.lang,
                            requireInteraction: true,
                            tag: payload.messageId,
                            vibrate:true,
                        });

                    registration.update();
                }, 100);
            })
            .catch(function (err) {
                console.log("Service Worker Failed to Register", err);
            })
    }
});
function saveToken(currentToken){
    const url = window.location.href;
    const path = new URL(url).pathname;
    let api = null;

    const containsCustomer = path.startsWith('/customer');
    const containsSeller = path.startsWith('/seller');
    const containsAdmin = path.startsWith('/cp-admin');

    if (containsCustomer) {
        api = 'customer';
    } else if (containsSeller) {
        api = 'seller';
    } else if (containsAdmin) {
        api = 'admin';
    }
    if(api != null) {
        axios.post('/' + api + '/token', {
            token: currentToken,
        })
            .then(function (response) {
            })
            .catch(function (error) {
            });
    }
}
function requestPermission() {
    if (Notification.permission !== "granted") {
        console.log('Requesting permission...');
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                console.log('Notification permission granted.');
            }
        });
    }
}
