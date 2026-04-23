import './stimulus_bootstrap.js';
import './styles/app.css';
import './chat.js';
import 'preline';

// Initialize Preline components
document.addEventListener('DOMContentLoaded', () => {
  window.HSStaticMethods?.autoInit();
});
