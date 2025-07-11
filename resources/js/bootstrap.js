// 引入 Bootstrap 的核心 JavaScript
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// (Axios 的設定維持不變)
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';