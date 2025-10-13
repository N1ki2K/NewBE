import * as React from 'react';
import { cn } from './ui/utils';

export interface IconProps extends React.SVGProps<SVGSVGElement> {
  className?: string;
}

const baseIconClass = 'stroke-current';

function IconBase({
  className,
  children,
  viewBox = '0 0 24 24',
  ...props
}: React.PropsWithChildren<IconProps>) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
      className={cn(baseIconClass, className)}
      viewBox={viewBox}
      {...props}
    >
      {children}
    </svg>
  );
}

export function ArrowLeftIcon({ className, ...props }: IconProps) {
  return (
    <IconBase className={className} {...props}>
      <path d="M19 12H5" />
      <path d="m12 19-7-7 7-7" />
    </IconBase>
  );
}

export function CalendarIcon({ className, ...props }: IconProps) {
  return (
    <IconBase className={className} {...props}>
      <path d="M8 2v4" />
      <path d="M16 2v4" />
      <rect width="18" height="18" x="3" y="4" rx="2" />
      <path d="M3 10h18" />
    </IconBase>
  );
}

export function FileTextIcon({ className, ...props }: IconProps) {
  return (
    <IconBase className={className} {...props}>
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
      <path d="M14 2v6h6" />
      <path d="M16 13H8" />
      <path d="M16 17H8" />
    </IconBase>
  );
}

