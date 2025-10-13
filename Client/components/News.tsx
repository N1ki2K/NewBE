import * as React from 'react';
import { Button } from './ui/button';
import { Separator } from './ui/separator';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { cn } from './ui/utils';
import {
  ArrowLeftIcon,
  CalendarIcon,
  FileTextIcon,
} from './icons';

export interface NewsArticleData {
  title: string;
  subtitle?: string;
  date: string;
  dateTime?: string;
  author: string;
  image?: string;
  imageAlt?: string;
  content: string[];
  htmlContent?: string;
  pdfUrl?: string;
  pdfFileName?: string;
  isFeatured?: boolean;
}

interface NewsLabels {
  backToNews: string;
  authorLabel: string;
  featuredLabel: string;
  downloadDocument: string;
  downloadDescription: string;
  noImage: string;
  footer: string;
}

interface NewsProps {
  article: NewsArticleData;
  onBack?: () => void;
  labels: NewsLabels;
}

export function News({ article, onBack, labels }: NewsProps) {
  const handlePdfDownload = React.useCallback(() => {
    if (article.pdfUrl) {
      window.open(article.pdfUrl, '_blank', 'noopener,noreferrer');
    }
  }, [article.pdfUrl]);

  const showHtmlContent =
    typeof article.htmlContent === 'string' &&
    article.htmlContent.trim().length > 0;

  const hasParagraphs = article.content && article.content.length > 0;

  return (
    <div className="min-h-screen bg-gray-50 text-slate-900">
      <header className="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-4xl items-center px-4 py-4 sm:px-6 lg:px-8">
          <Button
            variant="ghost"
            className={cn(
              'gap-2 text-brand-blue hover:bg-brand-blue/10',
              !onBack && 'pointer-events-none opacity-50',
            )}
            onClick={onBack}
            disabled={!onBack}
          >
            <ArrowLeftIcon className="h-4 w-4" />
            {labels.backToNews}
          </Button>
        </div>
      </header>

      <main className="mx-auto flex w-full max-w-4xl flex-1 flex-col px-4 py-10 sm:px-6 lg:px-8">
        <article className="flex flex-1 flex-col gap-10">
          <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <ImageWithFallback
              src={article.image}
              alt={article.imageAlt || article.title}
              className="h-64 w-full object-cover sm:h-80"
              fallback={
                <div className="flex h-64 w-full items-center justify-center bg-gray-100 text-sm text-gray-500 sm:h-80">
                  {labels.noImage}
                </div>
              }
            />
          </div>

          <div className="space-y-6">
            <div className="space-y-3">
              <div className="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                <div className="inline-flex items-center gap-2">
                  <CalendarIcon className="h-4 w-4" />
                  <time dateTime={article.dateTime || article.date}>
                    {article.date}
                  </time>
                </div>
                <Separator className="hidden h-4 w-px bg-gray-300 sm:block" />
                <span className="inline-flex items-center gap-2 text-gray-600">
                  {labels.authorLabel} {article.author}
                </span>
                {article.isFeatured && (
                  <span className="inline-flex items-center gap-2 rounded-full bg-brand-gold/20 px-3 py-1 text-xs font-semibold text-brand-blue">
                    {labels.featuredLabel}
                  </span>
                )}
              </div>

              <h1 className="text-3xl font-bold text-brand-blue sm:text-4xl">
                {article.title}
              </h1>

              {article.subtitle && (
                <p className="text-lg text-gray-600">{article.subtitle}</p>
              )}
            </div>

            <Separator />

            {showHtmlContent ? (
              <div
                className="prose max-w-none text-gray-700 prose-headings:text-brand-blue"
                dangerouslySetInnerHTML={{ __html: article.htmlContent || '' }}
              />
            ) : (
              <div className="prose max-w-none text-gray-700 prose-headings:text-brand-blue">
                {hasParagraphs ? (
                  article.content.map((paragraph, index) => (
                    <p key={index} className="leading-relaxed">
                      {paragraph}
                    </p>
                  ))
                ) : (
                  <p className="italic text-gray-500">—</p>
                )}
              </div>
            )}

            {article.pdfUrl && (
              <div className="space-y-6 rounded-lg border border-brand-blue/20 bg-brand-blue/5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                  <div className="flex flex-1 items-start gap-4">
                    <div className="rounded-lg bg-brand-blue p-3 text-white">
                      <FileTextIcon className="h-6 w-6" />
                    </div>
                    <div className="space-y-2">
                      <h3 className="text-lg font-semibold text-brand-blue">
                        {labels.downloadDocument}
                      </h3>
                      <p className="text-sm text-gray-600">
                        {labels.downloadDescription}
                      </p>
                      {article.pdfFileName && (
                        <p className="text-sm font-medium text-brand-blue">
                          {article.pdfFileName}
                        </p>
                      )}
                    </div>
                  </div>

                  <Button
                    className="gap-2 bg-brand-blue text-white hover:bg-brand-blue-light"
                    onClick={handlePdfDownload}
                  >
                    <FileTextIcon className="h-4 w-4" />
                    {article.pdfFileName
                      ? `${labels.downloadDocument} (${article.pdfFileName})`
                      : labels.downloadDocument}
                  </Button>
                </div>
              </div>
            )}
          </div>
        </article>
      </main>

      <footer className="border-t border-gray-200 bg-white/80">
        <div className="mx-auto max-w-4xl px-4 py-6 text-center text-sm text-gray-500 sm:px-6 lg:px-8">
          {labels.footer}
        </div>
      </footer>
    </div>
  );
}
