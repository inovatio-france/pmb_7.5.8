//1 Bouger le panier
function moveResumePanier() {
    var resumePanier = document.querySelector("#resume_panier");
    var mobileBasket = document.querySelector("#pmb_mobile_basket"); //� modifier
    var containerPanier = document.querySelector(".pmb_container_panier");//� modifier

    // d�placer le panier en dessous de 960px
    if (window.innerWidth < 960) {
        mobileBasket.appendChild(resumePanier);
    } else {
        containerPanier.appendChild(resumePanier);
    }
}

// Au chargement initial de la page
moveResumePanier();

// Lorsque la fen�tre est redimensionn�e
window.addEventListener("resize", moveResumePanier);





//2 Bouger l'aide � la connexion
const connexion = document.querySelector('#pmb_help_connexion');
const form = document.querySelector('#login_form');

// Ins�rer l'�l�ment � c�t� de la r�f�rence
function moove_connexion() {

    if (form && connexion) {
        form.insertAdjacentElement('afterend', connexion); 
    }
   
}

// Au chargement initial de la page
moove_connexion();

// Lorsque la fen�tre est redimensionn�e
window.addEventListener("resize", moove_connexion);




//3 Bouger le resultat de recherche
const elementToMove = document.querySelector('.segment_search_results');
const referenceElement = document.querySelector('.new_search_segment_title');

// Ins�rer l'�l�ment � c�t� de la r�f�rence
function insererElementACoteDeReference() {

    if (referenceElement && elementToMove) {
        referenceElement.insertAdjacentElement('afterend', elementToMove); 
    }
   
}

// Appeler la fonction pour ins�rer l'�l�ment
insererElementACoteDeReference();




//4 Bouger les segments
const segments = document.querySelector('#search_universe_segments_list');
const search_container = document.querySelector('#segment_form_container');

// Ins�rer l'�l�ment � c�t� de la r�f�rence
function moove_segment() {

    if (search_container && segments) {
    search_container.insertAdjacentElement('afterend', segments);
    }
    
}

// Appeler la fonction pour ins�rer l'�l�ment
moove_segment();

// Lorsque la fen�tre est redimensionn�e
window.addEventListener("resize", moove_segment);




