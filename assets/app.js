import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// Like et unlike

    document.addEventListener('DOMContentLoaded', function() {

// Like et unlike

    const likeButton = document.getElementById('likeButton');
    const unlikeButton = document.getElementById('unlikeButton');
    const likeForm = document.getElementById('likeForm');
    const unlikeForm = document.getElementById('unlikeForm');

// Favoris
    const favoriteButton = document.getElementById('favoriteButton');
    const unfavoriteButton = document.getElementById('unfavoriteButton');
    const favoriteForm = document.getElementById('favoriteForm');
    const unfavoriteForm = document.getElementById('unfavoriteForm');

// Like et unlike

    if (likeButton) {
        likeButton.addEventListener('click', function(e) {
            e.preventDefault();
            likeForm.submit();
            likeButton.id = 'unlikeButton';
            likeForm.id = 'unlikeForm';
        });
    }

    if (unlikeButton) {
        unlikeButton.addEventListener('click', function(e) {
            e.preventDefault();
            unlikeForm.submit();
            unlikeButton.id = 'likeButton';
            unlikeForm.id = 'likeForm';
        });
    }

// Favoris

    if (favoriteButton) {
        favoriteButton.addEventListener('click', function(e) {
            e.preventDefault();
            favoriteForm.submit();
            favoriteButton.id = 'unfavoriteButton';
            favoriteForm.id = 'unfavoriteForm';
        });
    }

    if (unfavoriteButton) {
        unfavoriteButton.addEventListener('click', function(e) {
            e.preventDefault();
            unfavoriteForm.submit();
            unfavoriteButton.id = 'favoriteButton';
            unfavoriteForm.id = 'favoriteForm';
        });
    }
});