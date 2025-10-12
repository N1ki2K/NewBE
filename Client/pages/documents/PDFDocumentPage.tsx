import React from 'react';
import { useParams } from 'react-router-dom';
import PageWrapper from '../../components/PageWrapper';
import InlinePDFViewer from '../../components/InlinePDFViewer';
import { useLanguage } from '../../context/LanguageContext';

const PDFDocumentPage: React.FC = () => {
  const { filename = '' } = useParams<{ filename: string }>();
  const { getTranslation } = useLanguage();

  const pageTitle = getTranslation('pdfViewer.pageTitle', 'Document Viewer');
  const decodedFilename = filename ? decodeURIComponent(filename) : '';

  return (
    <PageWrapper title={pageTitle}>
      <div className="space-y-6">
        <p className="text-gray-600">
          {getTranslation('pdfViewer.currentDocument', 'Currently viewing:')}{' '}
          <span className="font-semibold text-gray-800">{decodedFilename || getTranslation('pdfViewer.unknownDocument', 'Unknown document')}</span>
        </p>
        <InlinePDFViewer filename={filename} />
      </div>
    </PageWrapper>
  );
};

export default PDFDocumentPage;
