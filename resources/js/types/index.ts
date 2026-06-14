export interface User {
    id: number;
    name: string;
    email: string;
}

export interface FlashMessages {
    success: string | null;
    error: string | null;
}

export interface Auth {
    user: User | null;
}

export interface SharedProps {
    auth: Auth;
    flash: FlashMessages;
    errors: Record<string, string>;
    [key: string]: unknown;
}
