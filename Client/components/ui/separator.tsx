import * as React from 'react';
import { cn } from './utils';

export interface SeparatorProps extends React.HTMLAttributes<HTMLDivElement> {}

export function Separator({ className, ...props }: SeparatorProps) {
  return (
    <div
      className={cn('h-px w-full bg-gray-200', className)}
      role="separator"
      {...props}
    />
  );
}

