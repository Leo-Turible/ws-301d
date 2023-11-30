const menuBtn = document.querySelector('.menu-btn');
const monMenu = document.querySelector("#monMenu")
let menuOpen = false;
menuBtn.addEventListener('click', () => {
    if(!menuOpen) {
        menuBtn.classList.add('open');
        // menuBtn.classList.add('mon-menu1');
        //monMenu.classList.replace('mon-menu','mon-menu1');
        monMenu.classList.add('mobile-menu');
        menuOpen = true;
    } else {
        menuBtn.classList.remove('open');
        // monMenu.classList.replace('mon-menu','mon-menu1');
        monMenu.classList.remove('mobile-menu');
        menuOpen = false;
    }
});


// const menuHamburger = document.querySelector(".menu-btn")
// const monMenu = document.querySelector("#monMenu")
// menuHamburger.addEventListener('click', ()=>{
//     // monMenu.classList.toggle('mobile-menu')
//     if(menuHamburger = document.querySelector(".open")) {
//         menuHamburger.classList.add('mobile-menu')
//     } else {
//         menuHamburger.classList.remove('mobile-menu')
//     }
// })


// const menuToggle = document.querySelector('.menu-toggle input');
// const menu = document.querySelector('.menu');

// menuToggle.addEventListener('click', function() {
//   menu.classList.toggle('active');
// });

console.log('burger.js bien reçu !')