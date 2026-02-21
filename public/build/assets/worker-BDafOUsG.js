import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-app.js";

import { onBackgroundMessage, getMessaging as swGetMessaging  } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-messaging-sw.js";

const firebaseApp = initializeApp({
    apiKey: "AIzaSyAyAhG0DihgjoQkkMP54fkmdI9oU6UzT-o",
    authDomain: "evento-5110e.firebaseapp.com",
    projectId: "evento-5110e",
    storageBucket: "evento-5110e.appspot.com",
    messagingSenderId: "846696493156",
    appId: "1:846696493156:web:7c4640c981a69a3952af71",
    measurementId: "G-PZPSBRSFWE"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = swGetMessaging(firebaseApp);

onBackgroundMessage(messaging, (payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
});



self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    let url = null;

    url = event.notification.data?.url;

    if (url == null ) {
        url = event.notification.data?.FCM_MSG?.notification?.data?.url;

    }
    if (url == null ) {
        url = '/';

    }

    clients.openWindow(url);
}, false);
