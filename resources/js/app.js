import './bootstrap';

// Import jQuery and dependencies
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import Popper.js
import Popper from 'popper.js';
window.Popper = Popper;

// Import Bootstrap 4.6
import 'bootstrap';

// Import AdminLTE
import 'admin-lte';

// Import OverlayScrollbars
import { OverlayScrollbars } from 'overlayscrollbars';

// Import Chart.js
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
} from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

window.Chart = Chart;

// Import SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Configure Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize Bootstrap popovers
    $('[data-toggle="popover"]').popover();
    
    // Initialize OverlayScrollbars for sidebar
    const sidebarElement = document.querySelector('.main-sidebar .sidebar');
    if (sidebarElement) {
        OverlayScrollbars(sidebarElement, {
            scrollbars: {
                autoHide: 'leave',
                autoHideDelay: 1300
            }
        });
    }
    
    // Initialize OverlayScrollbars for content wrapper
    const contentElement = document.querySelector('.content-wrapper');
    if (contentElement) {
        OverlayScrollbars(contentElement, {
            scrollbars: {
                autoHide: 'leave',
                autoHideDelay: 1300
            }
        });
    }
});

// TODO: Add Livewire integration when components are created
// TODO: Add hotspot portal specific JavaScript functionality