import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

import AutoNetwork from './ts/pages/AutoNetwork';

const queryClient = new QueryClient();

const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      {/* <VitePage /> */}
      {/* <Database /> */}
      <AutoNetwork />
    </QueryClientProvider>
  );
};

export default App;
