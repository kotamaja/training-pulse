export type LoginFormValue = {
  email: string;
  password: string;
};

export function createEmptyLoginFormValue(): LoginFormValue {
  return {
    email: '',
    password: '',
  };
}
