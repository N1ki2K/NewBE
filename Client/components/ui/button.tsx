import * as React from 'react';
import { cn } from './utils';

type ButtonVariant = 'default' | 'ghost';

const variantStyles: Record<ButtonVariant, string> = {
  default:
    'bg-brand-blue text-white hover:bg-brand-blue-light focus-visible:ring-brand-blue',
  ghost:
    'bg-transparent text-brand-blue hover:bg-brand-blue/10 focus-visible:ring-brand-blue/40',
};

const baseStyles =
  'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-60';

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'default', type = 'button', ...props }, ref) => (
    <button
      ref={ref}
      type={type}
      className={cn(baseStyles, variantStyles[variant], className)}
      {...props}
    />
  ),
);

Button.displayName = 'Button';

