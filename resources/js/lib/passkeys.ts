import {
    startAuthentication,
    startRegistration,
    type PublicKeyCredentialCreationOptionsJSON,
    type PublicKeyCredentialRequestOptionsJSON,
} from '@simplewebauthn/browser';

const jsonHeaders = (csrfToken: string): HeadersInit => ({
    Accept: 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    'X-Requested-With': 'XMLHttpRequest',
});

async function errorMessage(res: Response, fallback: string): Promise<string> {
    const data = (await res.json().catch(() => ({}))) as { message?: string };
    return data.message ?? fallback;
}

/** POST and parse JSON, throwing the server message on a non-2xx response. */
async function postJson(
    url: string,
    body: unknown,
    csrfToken: string,
): Promise<Record<string, unknown>> {
    const res = await fetch(url, {
        method: 'POST',
        headers: { ...jsonHeaders(csrfToken), 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
        credentials: 'same-origin',
    });
    if (!res.ok) {
        throw new Error(await errorMessage(res, 'The request failed.'));
    }
    return (await res.json().catch(() => ({}))) as Record<string, unknown>;
}

/** Fetch WebAuthn options (server stashes the challenge in the session). */
async function fetchOptions<T>(url: string, csrfToken: string): Promise<T> {
    const res = await fetch(url, {
        method: 'POST',
        headers: jsonHeaders(csrfToken),
        credentials: 'same-origin',
    });
    if (!res.ok) {
        throw new Error(await errorMessage(res, 'Could not fetch the options.'));
    }
    return (await res.json()) as T;
}

/** Register a new passkey for the signed-in user. */
export async function registerPasskey(name: string, csrfToken: string): Promise<void> {
    const optionsJSON = await fetchOptions<PublicKeyCredentialCreationOptionsJSON>(
        '/settings/passkeys/options',
        csrfToken,
    );
    const response = await startRegistration({ optionsJSON });
    await postJson('/settings/passkeys', { name, response }, csrfToken);
}

/** Delete a passkey by id. */
export async function deletePasskey(id: number, csrfToken: string): Promise<void> {
    const res = await fetch(`/settings/passkeys/${id}`, {
        method: 'DELETE',
        headers: jsonHeaders(csrfToken),
        credentials: 'same-origin',
    });
    if (!res.ok) {
        throw new Error(await errorMessage(res, 'Delete failed.'));
    }
}

/** Authenticate with a discoverable passkey; resolves to the redirect URL. */
export async function loginWithPasskey(csrfToken: string): Promise<string> {
    const optionsJSON = await fetchOptions<PublicKeyCredentialRequestOptionsJSON>(
        '/user/passkeys/options',
        csrfToken,
    );
    const response = await startAuthentication({ optionsJSON });
    const data = await postJson('/user/passkeys/login', { response }, csrfToken);
    return typeof data.redirect === 'string' ? data.redirect : '/dashboard';
}
