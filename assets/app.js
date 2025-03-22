import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import { Alert, Popover, Toast } from 'bootstrap';
import * as bootstrap from 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.scss';
window.bootstrap = bootstrap;

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


  