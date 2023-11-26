import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

import Database from './ts/pages/Database';

const queryClient = new QueryClient();

const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      {/* <VitePage /> */}
      <Database />
    </QueryClientProvider>
  );
};

export default App;
