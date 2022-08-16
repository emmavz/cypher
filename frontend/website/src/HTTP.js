import axios from "axios";

// create a new axios instance
const instance = axios.create({
  baseURL: apiRoot,
  headers: {
    "Content-Type": "application/json",
  },
});

// const loader = document.getElementById('loader');

// before a request is made start the nprogress
instance.interceptors.request.use((config) => {
  // NProgress.start()
  // loader.classList.remove('hide');
  return config;
});

// before a response is returned stop nprogress
instance.interceptors.response.use((response) => {
  // NProgress.done()
  // loader.classList.add('hide');
  return response;
});

export { axios, instance };
