(function (window) {
  'use strict';

  class ApiError extends Error {
    constructor(message, options) {
      super(message);
      this.name = 'ApiError';
      this.status = options && options.status ? options.status : 0;
      this.payload = options && options.payload ? options.payload : null;
    }
  }

  const resolveUrl = function (path) {
    if (/^https?:\/\//i.test(path)) {
      return path;
    }

    const normalizedPath = path.charAt(0) === '/' ? path : '/' + path;
    return CONFIG.API_BASE_URL + normalizedPath;
  };

  const getToken = function () {
    return localStorage.getItem(CONFIG.STORAGE_KEYS.token);
  };

  const setToken = function (token) {
    localStorage.setItem(CONFIG.STORAGE_KEYS.token, token);
  };

  const clearToken = function () {
    localStorage.removeItem(CONFIG.STORAGE_KEYS.token);
  };

  const buildHeaders = function (headers, withAuth) {
    const result = new Headers(headers || {});

    if (!result.has('Accept')) {
      result.set('Accept', 'application/json');
    }

    if (withAuth) {
      const token = getToken();
      if (token) {
        result.set('Authorization', 'Bearer ' + token);
      }
    }

    return result;
  };

  const parseJsonResponse = async function (response) {
    const raw = await response.text();
    let payload = null;

    if (raw) {
      try {
        payload = JSON.parse(raw);
      } catch (error) {
        const start = raw.indexOf('{');
        const end = raw.lastIndexOf('}');

        if (start === -1 || end <= start) {
          throw new ApiError('La API devolvio una respuesta no JSON.', {
            status: response.status,
            payload: raw,
          });
        }

        payload = JSON.parse(raw.slice(start, end + 1));
      }
    }

    if (!response.ok || (payload && payload.status === 'error')) {
      throw new ApiError(
        (payload && payload.message) || 'La solicitud no pudo completarse.',
        {
          status: response.status,
          payload,
        }
      );
    }

    if (payload && Object.prototype.hasOwnProperty.call(payload, 'data')) {
      return payload.data;
    }

    return payload;
  };

  const request = async function (path, options) {
    const settings = options || {};
    const headers = buildHeaders(settings.headers, settings.auth === true);
    let body = settings.body;

    if (
      body &&
      typeof body === 'object' &&
      !(body instanceof FormData) &&
      !(body instanceof URLSearchParams) &&
      !(body instanceof Blob)
    ) {
      if (!headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
      }
      body = JSON.stringify(body);
    }

    const response = await fetch(resolveUrl(path), {
      method: settings.method || 'GET',
      headers,
      body,
    });

    try {
      return await parseJsonResponse(response);
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) {
        clearToken();
        if (window.location.pathname !== CONFIG.ROUTES.login) {
          window.location.href = CONFIG.ROUTES.login;
        }
      }
      throw error;
    }
  };

  const userResource = function (resource) {
    return request(CONFIG.ENDPOINTS.users + '?api=' + encodeURIComponent(resource), {
      auth: true,
    });
  };

  const login = async function (username, password) {
    const encryptedPayload = await cifrarLogin(username, password);
    const token = await request(CONFIG.ENDPOINTS.auth, {
      method: 'POST',
      headers: {
        'Content-Type': 'text/plain; charset=UTF-8',
      },
      body: encryptedPayload,
    });

    setToken(token);
    return token;
  };

  const requireAuth = function () {
    if (!getToken()) {
      window.location.href = CONFIG.ROUTES.login;
      return false;
    }

    return true;
  };

  const logout = function () {
    clearToken();
    window.location.href = CONFIG.ROUTES.login;
  };

  window.Cuspide = window.Cuspide || {};
  window.Cuspide.ApiError = ApiError;
  window.Cuspide.api = {
    request,
    login,
    users: userResource,
    profile: function () {
      return userResource('profile');
    },
    institucion: function () {
      return userResource('institucion');
    },
    menu: function () {
      return userResource('menu');
    },
  };
  window.Cuspide.auth = {
    getToken,
    setToken,
    clearToken,
    requireAuth,
    logout,
  };

  window.authFetch = function (url, options) {
    const settings = options || {};
    const headers = buildHeaders(settings.headers, true);

    return fetch(resolveUrl(url), Object.assign({}, settings, { headers }));
  };
})(window);
