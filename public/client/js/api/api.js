export class API {

    static get(url) {
        return this.request(url);
    }

    static post(url, data) {
        return this.request(url, {
            method: "POST",
            body: data
        });
    }

    static patch(url, data) {
        return this.request(url, {
            method: "PATCH",
            body: data
        });
    }

    static delete(url, data) {
        return this.request(url, {
            method: "DELETE",
            body: data
        });
    }

    static async request(url, options = {}) {
        if (options.body && !(options.body instanceof FormData)) {
            options.headers = {
                ...options.headers,
                "Content-Type": "application/json"
            };
            options.body = JSON.stringify(options.body);
        }

        const response = await fetch(`${APP_URLROOT}/${url}`, options);

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        return await response.json();
    }
}
