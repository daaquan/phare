export interface User {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    is_admin?: boolean;
}

export interface FlashMessages {
    success: string | null;
    error: string | null;
}

export interface Auth {
    user: User | null;
}

export interface Passkey {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string | null;
}

export interface SharedProps {
    auth: Auth;
    flash: FlashMessages;
    errors: Record<string, string>;
    csrf_token: string;
    [key: string]: unknown;
}
