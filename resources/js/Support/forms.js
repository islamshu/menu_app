/**
 * Helpers for submitting Inertia `useForm()` instances.
 */

/**
 * Submit a form as `multipart/form-data`.
 *
 * PHP only parses multipart bodies on POST requests, so a PUT/PATCH submission
 * that carries FormData (any form using `forceFormData` or a file upload) is
 * sent as POST with a spoofed `_method` field. Without this the server receives
 * an empty body and every required field fails validation.
 *
 * @param {import('@inertiajs/vue3').InertiaForm<Record<string, any>>} form
 * @param {'post'|'put'|'patch'} method
 * @param {string} url
 * @param {Record<string, any>} [options]
 */
export function submitMultipart(form, method, url, options = {}) {
    const verb = String(method).toLowerCase();

    if (verb === 'post') {
        form.post(url, { forceFormData: true, ...options });
        return;
    }

    form
        .transform((data) => ({ ...data, _method: verb }))
        .post(url, { forceFormData: true, ...options });
}
