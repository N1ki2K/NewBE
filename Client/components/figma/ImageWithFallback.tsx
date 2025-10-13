import * as React from 'react';
import { cn } from '../ui/utils';

export interface ImageWithFallbackProps
  extends React.ImgHTMLAttributes<HTMLImageElement> {
  fallback?: React.ReactNode;
}

export function ImageWithFallback({
  fallback,
  className,
  onError,
  src,
  alt,
  ...props
}: ImageWithFallbackProps) {
  const [hasError, setHasError] = React.useState(false);

  const handleError = React.useCallback<React.ReactEventHandler<HTMLImageElement>>(
    (event) => {
      setHasError(true);
      onError?.(event);
    },
    [onError],
  );

  if (!src || hasError) {
    return (
      <div
        className={cn(
          'flex h-full w-full items-center justify-center bg-gray-100 text-sm text-gray-500',
          className,
        )}
      >
        {fallback ?? 'Image unavailable'}
      </div>
    );
  }

  return (
    <img
      src={src}
      alt={alt}
      className={className}
      onError={handleError}
      {...props}
    />
  );
}

