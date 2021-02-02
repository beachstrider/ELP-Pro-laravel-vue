const DEFAULT_OPTIONS = {
    placement: 'topRight',
    message:'',
    description:''
}

export const loginSuccess = ($nt, description = 'Welcome back!') => $nt.success({...DEFAULT_OPTIONS, message: 'Login Successful', description: description})
export const loginFailed = ($nt, description = 'Try Again!') => $nt.error({...DEFAULT_OPTIONS, message: 'Login failed', description: description})
export const sessionExpires = ($nt, description = 'Please login again') => $nt.error({...DEFAULT_OPTIONS, message: 'Session expired!', description: description})
export const serverError = ($nt, description = 'Please refresh the page and try again!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops! Server Error!', description: description})
export const notFound = ($nt, description = 'Unable to Find Data!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops! Error!', description: description})

export const verifyEmailSuccess = ($nt, description = 'You can login now.') => $nt.success({...DEFAULT_OPTIONS, message: 'Account Verification Successful', description: description})
export const verifyEmailFailed = ($nt, description = 'Try Again!') => $nt.error({...DEFAULT_OPTIONS, message: 'Account Verification failed', description: description})

export const forgotPasswordSuccess = ($nt, description = 'Check inbox! We have sent you email.') => $nt.success({...DEFAULT_OPTIONS, message: description})
export const resetPasswordSuccess = ($nt, description = 'You can login now') => $nt.success({...DEFAULT_OPTIONS, message: 'Congratulations! Reset password successful.', description: description})

export const itemAdded = ($nt, description = 'Item successfully added') => $nt.success({...DEFAULT_OPTIONS, message: 'Congratulations!', description: description})
export const itemUpdated = ($nt, description = 'Item successfully updated') => $nt.warning({...DEFAULT_OPTIONS, message: '', description: description})
export const itemEditFails = ($nt, description = 'Unable to find Item!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops!', description: description})
export const itemDeleted = ($nt, description = 'Item successfully deleted!') => $nt.error({...DEFAULT_OPTIONS, message: '', description: description})
export const itemDeleteFails = ($nt, description = 'Unable to delete Item!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops!', description: description})
export const itemDeleteFailsBecsDependency = ($nt, description = 'Cannot delete item because other objects depend on it!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops!', description: description})

export const invalidUpload = ($nt, description = 'Not a valid upload file!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops!', description: description})
export const fileSizeLimitError = ($nt, description = 'File Size is too big! Try Again!') => $nt.error({...DEFAULT_OPTIONS, message: 'Whoops!', description: description})
