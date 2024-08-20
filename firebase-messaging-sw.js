importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js');




// Initialize Firebase
var config = {
  apiKey:"AIzaSyCr8Waf3WJjiWKsx-6BxBvLOKdGb3FXgQE" ,
  authDomain:"edemand-79907.firebaseapp.com" ,
  projectId:"edemand-79907" ,
  storageBucket:"edemand-79907.appspot.com" ,
  messagingSenderId:"811828125363" ,
  appId:"1:811828125363:web:5b177d6625c3e3731f5ac6",
  measurementId:"G-J4RWHNVBG0" 
};


firebase.initializeApp(config);
const fcm=firebase.messaging();
fcm.getToken({
    vapidKey:"BNh8i559_RYdfpgtS50AJPtUNrXCEPijSYd2LlYRhKpoWWt2KYjkAWudKAmz_sKwIhOZphj98TtnbFGU-SjfHPA"
}).then((token)=>{
    // console.log('getToken');
});



fcm.onBackgroundMessage((data)=>{
    // console.log('onBackgroundMessage - ',data);
})