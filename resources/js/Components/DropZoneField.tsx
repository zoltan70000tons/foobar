import React from 'react';
import { Button, FileTrigger, DropZone, Text } from 'react-aria-components';
import type { FileDropItem } from 'react-aria';

interface DropZoneFieldProps {
  onFilesChange: (files: File[]) => void;
  errors: Object | null;
}

const DropZoneField: React.FC<DropZoneFieldProps> = ({ onFilesChange, errors }) => {
  const [fileList, setFileList] = React.useState<File[]>([]);

  const handleDrop = (e: { items: DataTransferItem[] }) => {
    const files = e.items.filter((item) => item.kind === 'file') as FileDropItem[];
    const fileArray = files.map((file) => file.getAsFile()).filter((file): file is File => file !== null);
    setFileList(fileArray);
    onFilesChange(fileArray);
  };

  const handleSelect = (fileList: FileList) => {
    const files = Array.from(fileList);
    setFileList(files);
    onFilesChange(files);
  };

  return (
    <DropZone
      onDrop={handleDrop}
      style={{
        background: 'rgba(255, 255, 255, 0.05)',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        textAlign: 'center',
        padding: '24px',
        fontSize: '1rem',
        width: '100%',
        outline: 'none',
        border: '2px dashed #aaa',
        borderRadius: '8px',
        color: '#aaa',
        transition: 'border-color 0.3s ease',
        cursor: 'pointer',
      }}
      onDragEnter={(e) => {
        e.currentTarget.style.borderColor = '#fff';
      }}
      onDragLeave={(e) => {
        e.currentTarget.style.borderColor = '#aaa';
      }}
    >
      <FileTrigger allowsMultiple onSelect={handleSelect}>
        <Button style={{
          backgroundColor: '#3f51b5',
          color: '#fff',
          padding: '10px 20px',
          borderRadius: '4px',
          cursor: 'pointer',
          transition: 'background-color 0.3s ease',
          marginBottom: '1rem',
        }}>
          Select files
        </Button>
      </FileTrigger>
      <Text slot="label" style={{ display: 'block', marginTop: '1rem', color: '#fff' }}>
        {fileList.length > 0 ? fileList.map(file => file.name).join(', ') : 'Drop files here'}
      </Text>
      {errors && (
        <Text slot="label" style={{ display: 'block', marginTop: '1rem', color: '#f44336' }}>
          {errors.toString()}
        </Text>
      )}
    </DropZone>
  );
};

export default DropZoneField;
