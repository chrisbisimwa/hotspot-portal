// Import jQuery
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

// Import axios and configure
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Chart.js
import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
    PieController,
    ArcElement
} from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
    PieController,
    ArcElement
);

window.Chart = Chart;

// Import SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Initialize OverlayScrollbars
document.addEventListener('DOMContentLoaded', function() {
    // Initialize overlay scrollbars on sidebar
    const sidebar = document.querySelector('.main-sidebar .sidebar');
    if (sidebar) {
        OverlayScrollbars(sidebar, {
            scrollbars: {
                autoHide: 'scroll'
            }
        });
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-toggle="popover"]').popover();
});

// TODO: Livewire integration will be added later when components are created