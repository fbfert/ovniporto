import { usePage } from '@inertiajs/react';

/** What the page knows about the signed-in member (never the real name or e-mail). */
export interface AuthMember {
    nickname: string | null;
    avatarUrl: string | null;
    canOpenPanel: boolean;
    complete: boolean;
}

export function useAuthMember(): AuthMember | null {
    const { auth } = usePage().props as { auth?: { member: AuthMember | null } };
    return auth?.member ?? null;
}
