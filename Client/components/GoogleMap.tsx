import React, { useMemo } from "react";

interface MapProps {
  height?: number;
}

const SCHOOL_COORDINATES = {
  lat: 42.396025,
  lng: 25.641405,
};

const GoogleMap: React.FC<MapProps> = ({ height = 400 }) => {
  const { iframeSrc, externalLink } = useMemo(() => {
    const delta = 0.01;
    const west = SCHOOL_COORDINATES.lng - delta;
    const south = SCHOOL_COORDINATES.lat - delta;
    const east = SCHOOL_COORDINATES.lng + delta;
    const north = SCHOOL_COORDINATES.lat + delta;

    const bbox = `${west}%2C${south}%2C${east}%2C${north}`;
    const marker = `${SCHOOL_COORDINATES.lat}%2C${SCHOOL_COORDINATES.lng}`;

    return {
      iframeSrc: `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${marker}`,
      externalLink: `https://www.openstreetmap.org/?mlat=${SCHOOL_COORDINATES.lat}&mlon=${SCHOOL_COORDINATES.lng}#map=16/${SCHOOL_COORDINATES.lat}/${SCHOOL_COORDINATES.lng}`,
    };
  }, []);

  return (
    <div className="w-full">
      <div
        className="w-full overflow-hidden rounded-lg shadow-sm border border-gray-200"
        style={{ height }}
      >
        <iframe
          title="School location map"
          src={iframeSrc}
          width="100%"
          height="100%"
          style={{ border: 0 }}
          allowFullScreen
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        />
      </div>
      <p className="text-xs text-gray-500 mt-2 text-center">
        Map data ©{" "}
        <a
          href={externalLink}
          target="_blank"
          rel="noopener noreferrer"
          className="text-brand-blue hover:text-brand-gold-light"
        >
          OpenStreetMap contributors
        </a>
      </p>
    </div>
  );
};

export default GoogleMap;
