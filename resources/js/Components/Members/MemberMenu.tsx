import { Link, router } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useId, useRef, useState } from 'react';
import { t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';
import type { AuthMember } from './auth';

const copy = t.members;

function Avatar({ member }: { member: AuthMember }) {
    if (member.avatarUrl) {
        return (
            <img
                src={member.avatarUrl}
                alt=""
                referrerPolicy="no-referrer"
                className="size-9 rounded-full object-cover"
            />
        );
    }
    const initial = (member.nickname ?? '?').charAt(0).toUpperCase();
    return (
        <span className="inline-flex size-9 items-center justify-center rounded-full bg-beam font-display text-sm font-bold text-night">
            {initial}
        </span>
    );
}

/**
 * Avatar button with the account menu. The popover grows from the avatar
 * (top-right origin), closes on Esc, outside click and navigation.
 */
export function MemberMenu({ member }: { member: AuthMember }) {
    const [open, setOpen] = useState(false);
    const menuId = useId();
    const rootRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) return;
        const onPointer = (event: PointerEvent) => {
            if (!rootRef.current?.contains(event.target as Node)) setOpen(false);
        };
        const onKey = (event: KeyboardEvent) => event.key === 'Escape' && setOpen(false);
        document.addEventListener('pointerdown', onPointer);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('pointerdown', onPointer);
            document.removeEventListener('keydown', onKey);
        };
    }, [open]);

    const links = member.complete
        ? [
              { href: '/conta', label: copy.account },
              { href: '/conta?aba=relatos', label: copy.mySightings },
              { href: '/conta?aba=pedidos', label: copy.myOrders },
              ...(member.canOpenPanel ? [{ href: '/painel', label: copy.panel }] : []),
          ]
        : [{ href: '/boas-vindas', label: copy.finishProfile }];

    return (
        <div ref={rootRef} className="relative">
            <button
                type="button"
                aria-label={copy.menuLabel}
                aria-expanded={open}
                aria-controls={menuId}
                onClick={() => setOpen(!open)}
                className="inline-flex h-11 press items-center gap-2 rounded-full pr-3 pl-1 ring-1 ring-current/15 transition-colors hover:ring-current/30"
            >
                <Avatar member={member} />
                <span className="hidden max-w-28 truncate text-sm font-semibold sm:inline">
                    {member.nickname ?? copy.finishProfile}
                </span>
            </button>
            <AnimatePresence>
                {open && (
                    <motion.div
                        id={menuId}
                        initial={{ opacity: 0, transform: 'scale(0.96)' }}
                        animate={{ opacity: 1, transform: 'scale(1)', transition: { duration: 0.18, ease: ease.snap } }}
                        exit={{ opacity: 0, transform: 'scale(0.96)', transition: { duration: 0.12, ease: ease.snap } }}
                        style={{ transformOrigin: 'top right' }}
                        className="absolute top-[calc(100%+0.5rem)] right-0 z-10 w-56 rounded-[18px] bg-night-blue p-2 text-moonlight shadow-lift ring-1 ring-moonlight/12"
                    >
                        <ul>
                            {links.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        onClick={() => setOpen(false)}
                                        className="flex min-h-11 items-center rounded-xl px-3 text-[0.95rem] font-medium hover:bg-moonlight/8"
                                    >
                                        {link.label}
                                    </Link>
                                </li>
                            ))}
                            <li className="mt-1 border-t border-moonlight/10 pt-1">
                                <button
                                    type="button"
                                    onClick={() => router.post('/sair')}
                                    className="flex min-h-11 w-full items-center rounded-xl px-3 text-left text-[0.95rem] font-medium text-moonlight/75 hover:bg-moonlight/8"
                                >
                                    {copy.logout}
                                </button>
                            </li>
                        </ul>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
